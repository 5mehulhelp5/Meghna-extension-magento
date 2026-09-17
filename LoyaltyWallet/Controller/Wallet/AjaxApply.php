<?php

declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Controller\Wallet;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class AjaxApply implements HttpPostActionInterface
{
    public function __construct(
        private readonly JsonFactory $resultJsonFactory,
        private readonly RequestInterface $request,
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerSession $customerSession,
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): Json
    {
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Please log in.')
            ]);
        }

        $amount = (float)$this->request->getParam('wallet_amount', 0);

        if ($amount <= 0) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Please enter a valid wallet amount.')
            ]);
        }

        $customerId = (int)$this->customerSession->getCustomerId();

        try {

            $connection = $this->resourceConnection->getConnection();

            $tableName = $this->resourceConnection->getTableName(
                'codilar_store_wallet_ledger'
            );

            $select = $connection->select()
                ->from($tableName, ['balance_after'])
                ->where('customer_id = ?', $customerId)
                ->order('entity_id DESC')
                ->limit(1);

            $availableBalance = (float)$connection->fetchOne($select);

            if ($amount > $availableBalance) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __(
                        'Requested amount exceeds your available balance (₹%1).',
                        number_format($availableBalance, 2)
                    )
                ]);
            }

            $quote = $this->checkoutSession->getQuote();


            $previousWalletAmount = (float)$quote->getData(
                'applied_wallet_amount'
            );

            $quote->setData('applied_wallet_amount', 0);

            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();


            $orderTotal = (float)$quote->getGrandTotal();


            $minimumPayableAmount = 1.00;

            $maximumWalletAmount = $orderTotal - $minimumPayableAmount;

            if ($maximumWalletAmount <= 0) {

                $quote->setData(
                    'applied_wallet_amount',
                    $previousWalletAmount
                );

                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
                $quote->save();

                return $resultJson->setData([
                    'success' => false,
                    'message' => __(
                        'Wallet cannot be applied because the order must have at least ₹1.00 payable.'
                    )
                ]);
            }

            if ($amount > $maximumWalletAmount) {

                $quote->setData(
                    'applied_wallet_amount',
                    $previousWalletAmount
                );

                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
                $quote->save();

                return $resultJson->setData([
                    'success' => false,
                    'message' => __(
                        'You can use a maximum wallet amount of ₹%1. You must keep at least ₹1.00 payable.',
                        number_format($maximumWalletAmount, 2)
                    ),
                    'maximum_wallet_amount' => $maximumWalletAmount
                ]);
            }


            $quote->setData(
                'applied_wallet_amount',
                $amount
            );

            $quote->setTotalsCollectedFlag(false);


            $quote->collectTotals();


            $finalGrandTotal = (float)$quote->getGrandTotal();

            if ($finalGrandTotal < $minimumPayableAmount) {

                $quote->setData(
                    'applied_wallet_amount',
                    $previousWalletAmount
                );

                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
                $quote->save();

                return $resultJson->setData([
                    'success' => false,
                    'message' => __(
                        'Wallet amount cannot be applied. At least ₹1.00 must remain payable.'
                    )
                ]);
            }

            $quote->save();

            $this->checkoutSession->setData(
                'applied_wallet_amount',
                $amount
            );

            $this->logger->info(
                sprintf(
                    'Store Wallet: Applied ₹%s to quote ID %s for customer ID %s. Order total before wallet: ₹%s, final total: ₹%s',
                    number_format($amount, 2),
                    $quote->getId(),
                    $customerId,
                    number_format($orderTotal, 2),
                    number_format($finalGrandTotal, 2)
                )
            );

            return $resultJson->setData([
                'success' => true,
                'message' => __(
                    'Store wallet of ₹%1 applied successfully.',
                    number_format($amount, 2)
                ),
                'wallet_amount' => $amount,
                'grand_total' => $finalGrandTotal
            ]);

        } catch (\Exception $e) {

            $this->logger->error(
                'Store Wallet Error: ' . $e->getMessage()
            );

            return $resultJson->setData([
                'success' => false,
                'message' => __('Unable to apply wallet amount.')
            ]);
        }
    }
}
