<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class DeductWalletOnOrderPlace implements ObserverInterface
{
    public function __construct(private readonly ResourceConnection $resourceConnection, private readonly LoggerInterface $logger)
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

            $this->logger->info('WALLET DEDUCTION STARTED' . ' | Order ID: ' . $orderId . ' | Increment ID: ' . $incrementId . ' | Customer ID: ' . $customerId . ' | Wallet Amount: ' . $walletAmount);

            if ($walletAmount <= 0) {
                $this->logger->info('WALLET DEDUCTION SKIPPED: Wallet amount is zero.');

                return;
            }

            if ($customerId <= 0) {
                $this->logger->info('WALLET DEDUCTION SKIPPED: Customer ID is invalid.');

                return;
            }

            $connection = $this->resourceConnection->getConnection();

            $tableName = $this->resourceConnection->getTableName('codilar_store_wallet_ledger');

            /*
             * Get latest wallet balance.
             */
            $select = $connection->select()->from($tableName, ['balance_after'])->where('customer_id = ?', $customerId)->order('entity_id DESC')->limit(1);

            $currentBalance = $connection->fetchOne($select);

            $currentBalance = $currentBalance !== false ? (float)$currentBalance : 0.0;

            $this->logger->info('WALLET CURRENT BALANCE' . ' | Customer ID: ' . $customerId . ' | Balance: ' . $currentBalance);

            /*
             * Make sure wallet has enough balance.
             */
            if ($walletAmount > $currentBalance) {
                $this->logger->error('WALLET DEDUCTION FAILED' . ' | Requested: ' . $walletAmount . ' | Available: ' . $currentBalance);

                return;
            }

            $newBalance = $currentBalance - $walletAmount;

            /*
             * Comment for wallet transaction.
             */
            $comment = 'Wallet amount deducted from purchase - Order #' . $incrementId;

            /*
             * Create wallet debit transaction.
             */
            $connection->insert($tableName, ['customer_id' => $customerId, 'amount' => -$walletAmount, 'balance_after' => $newBalance, 'comment' => $comment]);

            $this->logger->info('WALLET DEDUCTION SUCCESSFUL' . ' | Order: ' . $incrementId . ' | Deducted: ' . $walletAmount . ' | Old Balance: ' . $currentBalance . ' | New Balance: ' . $newBalance . ' | Comment: ' . $comment);

        } catch (Throwable $e) {
            $this->logger->error('WALLET DEDUCTION ERROR: ' . $e->getMessage());
        }
    }
}
