<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Observer;

use Codilar\LoyaltyWallet\Logger\Logger as WalletLogger;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Throwable;

class DeductWalletOnOrderPlace implements ObserverInterface
{
    public function __construct(private readonly ResourceConnection $resourceConnection, private readonly WalletLogger $logger)
    {
    }

    public function execute(Observer $observer): void
    {
        try {
            $event = $observer->getEvent();
            $order = $event->getData('order');

            if (!$order) {
                $this->logger->error('WALLET DEDUCTION: Order is missing from event.');
                return;
            }

            $orderId = (int)$order->getId();
            $incrementId = (string)$order->getIncrementId();
            $customerId = (int)$order->getCustomerId();
            $walletAmount = (float)$order->getData('applied_wallet_amount');

            if ($walletAmount <= 0 || $customerId <= 0) {
                return; // Skip silently if no wallet amount was used or customer is invalid
            }

            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('codilar_store_wallet_ledger');

            // Start Transaction with For Update lock to prevent race conditions during checkout
            $connection->beginTransaction();

            try {
                // Get latest wallet balance with a lock
                $select = $connection->select()->from($tableName, ['balance_after'])->where('customer_id = ?', $customerId)->order('entity_id DESC')->limit(1)->forUpdate();

                $currentBalance = $connection->fetchOne($select);
                $currentBalance = $currentBalance !== false ? (float)$currentBalance : 0.0;

                if ($walletAmount > $currentBalance) {
                    throw new Exception(sprintf('Insufficient wallet balance. Requested: %.2f, Available: %.2f', $walletAmount, $currentBalance));
                }

                $newBalance = $currentBalance - $walletAmount;
                $comment = 'Wallet amount deducted from purchase - Order #' . $incrementId;

                // Create wallet debit transaction
                $connection->insert($tableName, ['customer_id' => $customerId, 'amount' => -$walletAmount, 'balance_after' => $newBalance, 'comment' => $comment]);

                $connection->commit();

                // Essential Financial Success Audit Log (Goes to var/log/codilar_wallet.log)
                $this->logger->info(sprintf('SUCCESS: Deducted ₹%.2f from Customer ID %d for Order #%s. Old Balance: ₹%.2f, New Balance: ₹%.2f', $walletAmount, $customerId, $incrementId, $currentBalance, $newBalance));

            } catch (Exception $innerEx) {
                $connection->rollBack();
                throw $innerEx;
            }

        } catch (Throwable $e) {
            // Essential Error Audit Log
            $this->logger->error(sprintf('FAILURE: Wallet deduction failed for Order #%s. Reason: %s', $incrementId ?? 'UNKNOWN', $e->getMessage()));
        }
    }
}
