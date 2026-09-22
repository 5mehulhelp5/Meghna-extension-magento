<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Block\Checkout;

use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Template;

class SuccessPoints extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly CheckoutSession $checkoutSession,
        private readonly LoyaltyLedgerRepositoryInterface $ledgerRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getPointsEarned(): int
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if (!$order || !$order->getId()) {
            return 0;
        }

        $this->searchCriteriaBuilder->addFilter('order_increment_id', $order->getIncrementId());
        $items = $this->ledgerRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        $total = 0;
        foreach ($items as $item) {
            $total += (int)$item->getQuantity();
        }
        return $total;
    }
}
