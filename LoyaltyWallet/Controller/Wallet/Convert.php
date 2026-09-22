<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Controller\Wallet;

use Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterfaceFactory;
use Codilar\LoyaltyWallet\Api\Data\StoreWalletInterfaceFactory;
use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Codilar\LoyaltyWallet\Api\StoreWalletRepositoryInterface;
use Codilar\LoyaltyWallet\Logger\Logger as WalletLogger;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class Convert implements HttpPostActionInterface
{
    public function __construct(
        private readonly RedirectFactory $resultRedirectFactory,
        private readonly RequestInterface $request,
        private readonly CustomerSession $customerSession,
        private readonly ManagerInterface $messageManager,
        private readonly LoyaltyLedgerRepositoryInterface $loyaltyLedgerRepository,
        private readonly StoreWalletRepositoryInterface $storeWalletRepository,
        private readonly LoyaltyLedgerInterfaceFactory $loyaltyLedgerFactory,
        private readonly StoreWalletInterfaceFactory $storeWalletFactory,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly WalletLogger $logger
    ) {
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('loyalty/wallet/index');

        // 1. Check if user is logged in
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $customerId = (int)$this->customerSession->getCustomerId();
        $coinsToConvert = (int)$this->request->getParam('coins_to_convert');

        if ($coinsToConvert <= 0) {
            $this->messageManager->addErrorMessage(__('Please specify a valid coin amount to convert.'));
            return $redirect;
        }

        try {
            // 2. Fetch the true available balance from the latest transaction's balance_after field
            $scBuilder = $this->searchCriteriaBuilderFactory->create();
            $scBuilder->addFilter('customer_id', $customerId);

            $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDescendingDirection()->create();
            $scBuilder->setSortOrders([$sortOrder]);
            $scBuilder->setPageSize(1);

            $transactions = $this->loyaltyLedgerRepository->getList($scBuilder->create())->getItems();
            $latestTransaction = !empty($transactions) ? reset($transactions) : null;

            $availableCoins = $latestTransaction ? (int)$latestTransaction->getBalanceAfter() : 0;
            $availableCoins = max(0, $availableCoins);

            if ($coinsToConvert > $availableCoins) {
                $this->messageManager->addErrorMessage(__('You do not have enough coins to convert this amount. (Available: %1)', $availableCoins));
                return $redirect;
            }

            // 3. Set Conversion Rule: 1 Coin = 1 Rupee (1:1 Ratio)
            $walletAmount = (float)$coinsToConvert;
            $newCoinBalance = max(0, $availableCoins - $coinsToConvert);

            // 4. Deduct Coins from Loyalty Ledger (Create negative entry)
            $loyaltyEntry = $this->loyaltyLedgerFactory->create();
            $loyaltyEntry->setCustomerId($customerId);
            $loyaltyEntry->setQuantity(-$coinsToConvert);
            $loyaltyEntry->setBalanceAfter($newCoinBalance);
            $loyaltyEntry->setComment('Converted to Store Wallet');
            $this->loyaltyLedgerRepository->save($loyaltyEntry);

            $walletScBuilder = $this->searchCriteriaBuilderFactory->create();
            $walletScBuilder->addFilter('customer_id', $customerId);
            $walletSortOrder = $this->sortOrderBuilder->setField('entity_id')->setDescendingDirection()->create();
            $walletScBuilder->setSortOrders([$walletSortOrder]);

            $walletList = $this->storeWalletRepository->getList($walletScBuilder->create())->getItems();
            $latestWallet = !empty($walletList) ? reset($walletList) : null;
            $currentWalletBalance = $latestWallet ? (float)$latestWallet->getBalanceAfter() : 0.0;
            $newWalletBalance = $currentWalletBalance + $walletAmount;

            $walletEntry = $this->storeWalletFactory->create();
            $walletEntry->setCustomerId($customerId);
            $walletEntry->setAmount($walletAmount);
            $walletEntry->setBalanceAfter($newWalletBalance);
            $walletEntry->setComment('Converted from Loyalty Coins');

            $this->storeWalletRepository->save($walletEntry);

            // Essential Financial Success Audit Log (Writes directly to var/log/codilar_wallet.log)
            $this->logger->info(sprintf(
                'SUCCESS: Customer ID %d converted %d coins into ₹%.2f wallet cash. New Coin Balance: %d, New Wallet Balance: ₹%.2f',
                $customerId,
                $coinsToConvert,
                $walletAmount,
                $newCoinBalance,
                $newWalletBalance
            ));

            $this->messageManager->addSuccessMessage(__('Successfully converted %1 coins into ₹%2 store wallet cash!', $coinsToConvert, number_format($walletAmount, 2)));
        } catch (\Exception $e) {

            $this->logger->error(sprintf(
                'FAILURE: Conversion failed for Customer ID %d. Reason: %s',
                $customerId,
                $e->getMessage()
            ));

            $this->messageManager->addErrorMessage(__('Conversion failed: %1', $e->getMessage()));
        }

        return $redirect;
    }
}
