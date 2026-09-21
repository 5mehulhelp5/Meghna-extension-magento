<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model\Service;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class WalletRedemptionService
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly LoggerInterface $logger
    ) {}

    public function redeemWalletBalance(int $customerId, float $amountToDeduct, ?string $orderIncrementId = null): void
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('codilar_store_wallet_ledger');

        // 1. Start Database Transaction
        $connection->beginTransaction();

        try {
            // 2. Select with pessimistic row-level lock (FOR UPDATE)
            // This blocks other simultaneous threads from reading/writing this customer's rows until commit/rollback
            $select = $connection->select()
                ->from($tableName)
                ->where('customer_id = ?', $customerId)
                ->order('entity_id DESC')
                ->limit(1)
                ->forUpdate(); // <--- CRITICAL: Locks the row against race conditions

            $latestRow = $connection->fetchRow($select);
            $currentBalance = $latestRow ? (float)$latestRow['balance_after'] : 0.00;

            // 3. Validate balance inside the locked window
            if ($currentBalance < $amountToDeduct) {
                throw new LocalizedException(__('Insufficient wallet balance.'));
            }

            $newBalance = $currentBalance - $amountToDeduct;

            // 4. Insert the new immutable ledger entry with the updated balance
            $connection->insert($tableName, [
                'customer_id' => $customerId,
                'order_increment_id' => $orderIncrementId,
                'amount' => -$amountToDeduct, // negative for deduction
                'balance_after' => $newBalance,
                'comment' => 'Deducted for Order #' . ($orderIncrementId ?? 'Checkout'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 5. Commit transaction (releases the lock)
            $connection->commit();

        } catch (\Exception $e) {
            // Rollback transaction if anything fails or balance is insufficient
            $connection->rollBack();
            $this->logger->error('Wallet Redemption Race Condition / Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
