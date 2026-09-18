<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model\Service;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class StoreWalletService
{
    public function __construct(private readonly ResourceConnection $resourceConnection, private readonly LoggerInterface $logger)
    {
    }

    /**
     * Safely deducts amount from the store wallet with race condition protection (Pessimistic Locking).
     *
     * @throws LocalizedException
     */
    public function createCompensatingCorrection(int $customerId, int $incorrectCoinsQuantity, string $reason): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $tableName = $this->resourceConnection->getTableName('codilar_loyalty_ledger');

            // 1. Lock the latest balance using FOR UPDATE to prevent race conditions during correction
            $select = $connection->select()
                ->from($tableName)
                ->where('customer_id = ?', $customerId)
                ->order('entity_id DESC')
                ->limit(1)
                ->forUpdate();

            $latestRow = $connection->fetchRow($select);
            $currentBalance = $latestRow ? (int)$latestRow['balance_after'] : 0;

            // 2. Invert the quantity to create the compensating effect
            // (e.g., if +100 was wrong, correction quantity is -100)
            $correctionQty = -$incorrectCoinsQuantity;
            $newBalance = $currentBalance + $correctionQty;

            // Ensure balance doesn't drop below zero due to correction (optional safety check)
            if ($newBalance < 0) {
                throw new LocalizedException(__('Correction would result in a negative balance.'));
            }

            // 3. Insert a brand new immutable ledger entry for the correction
            $connection->insert(
                $tableName,
                [
                    'customer_id' => $customerId,
                    'order_increment_id' => null,
                    'quantity' => $correctionQty,
                    'balance_after' => $newBalance,
                    'comment' => 'Correction: ' . $reason,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__('Failed to apply correction: %1', $e->getMessage()));
        }
    }
}
