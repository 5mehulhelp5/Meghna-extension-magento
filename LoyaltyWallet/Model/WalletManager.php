<?php
//declare(strict_types=1);
//
//namespace Codilar\Loyalty\Model;
//
//use Codilar\Loyalty\Api\WalletManagementInterface;
//use Magento\Quote\Api\CartRepositoryInterface;
//use Magento\Framework\Exception\LocalizedException;
//
//class WalletManager implements WalletManagementInterface
//{
//    /**
//     * @var CartRepositoryInterface
//     */
//    private CartRepositoryInterface $cartRepository;
//
//    public function __construct(
//        CartRepositoryInterface $cartRepository
//    ) {
//        $this->cartRepository = $cartRepository;
//    }
//
//    /**
//     * Apply wallet amount.
//     *
//     * @param int $cartId
//     * @param float $amount
//     * @return bool
//     * @throws LocalizedException
//     */
//    public function apply(
//        int $cartId,
//        float $amount
//    ): bool {
//        if ($amount <= 0) {
//            throw new LocalizedException(
//                __('Wallet amount must be greater than zero.')
//            );
//        }
//
//        $quote = $this->cartRepository->get($cartId);
//
//        /*
//         * Get customer's wallet balance.
//         *
//         * We will connect this with your existing
//         * StoreWalletRepository after checking its
//         * actual methods/fields.
//         */
//        $walletBalance = 0.0;
//
//        /*
//         * BR-02:
//         * Wallet balance cannot become negative.
//         */
//        if ($amount > $walletBalance) {
//            throw new LocalizedException(
//                __('You cannot redeem more than your wallet balance.')
//            );
//        }
//
//        /*
//         * BR-04:
//         * Checkout cap will be added here.
//         */
//        $checkoutCap = 0.0;
//
//        if ($checkoutCap > 0 && $amount > $checkoutCap) {
//            throw new LocalizedException(
//                __('You cannot redeem more than the checkout wallet limit.')
//            );
//        }
//
//        /*
//         * Wallet amount cannot exceed order total.
//         */
//        $grandTotal = (float) $quote->getGrandTotal();
//
//        if ($amount > $grandTotal) {
//            $amount = $grandTotal;
//        }
//
//        $quote->setData('wallet_amount', $amount);
//
//        $this->cartRepository->save($quote);
//
//        return true;
//    }
//
//    /**
//     * Remove wallet amount.
//     *
//     * @param int $cartId
//     * @return bool
//     */
//    public function remove(int $cartId): bool
//    {
//        $quote = $this->cartRepository->get($cartId);
//
//        $quote->setData('wallet_amount', 0);
//
//        $this->cartRepository->save($quote);
//
//        return true;
//    }
//}
