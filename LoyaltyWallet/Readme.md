# Codilar Loyalty Wallet Module for Magento 2

A robust Magento 2 module implementing a dual **Loyalty Coins** and **Store Cash Wallet** system.

The module provides loyalty coin management, wallet balance tracking, FIFO-based point expiration, configurable admin limits, append-only ledgers, compensating transactions, and transaction-level locking to prevent race conditions and double spending.

---

## Module Folder Structure

```text
Codilar/
└── LoyaltyWallet/
    ├── Api/
    │   ├── Data/
    │   │   ├── LoyaltyLedgerInterface.php
    │   │   └── StoreWalletInterface.php
    │   ├── LoyaltyLedgerRepositoryInterface.php
    │   └── StoreWalletRepositoryInterface.php
    │
    ├── Block/
    │   ├── Checkout/
    │   │   ├── SuccessPoints.php
    │   │   └── WalletApply.php
    │   └── Customer/
    │       ├── Coins.php
    │       └── StoreWallet.php
    │
    ├── Controller/
    │   ├── Coins/
    │   │   └── Index.php
    │   └── Wallet/
    │       ├── AjaxApply.php
    │       ├── Convert.php
    │       └── Index.php
    │
    ├── Cron/
    │   └── ExpirePoints.php
    │
    ├── Model/
    │   ├── ResourceModel/
    │   │   ├── LoyaltyLedger/
    │   │   │   └── Collection.php
    │   │   └── StoreWallet/
    │   │       └── Collection.php
    │   │
    │   ├── Service/
    │   │   └── WalletRedemptionService.php
    │   │
    │   ├── Total/
    │   │   └── Wallet.php
    │   │
    │   ├── LoyaltyLedger.php
    │   └── StoreWallet.php
    │
    ├── Observer/
    │   ├── AwardPointsOnOrderPlace.php
    │   ├── CopyWalletAmountToOrder.php
    │   ├── DeductWalletOnOrderPlace.php
    │   ├── ReverseLoyaltyPointsOnCancel.php
    │   └── TransferWalletAmountToOrder.php
    │
    ├── View/
    │   └── frontend/
    │       ├── layout/
    │       ├── templates/
    │       └── web/
    │
    ├── etc/
    │   ├── adminhtml/
    │   ├── frontend/
    │   ├── config.xml
    │   ├── crontab.xml
    │   ├── db_schema.xml
    │   ├── di.xml
    │   ├── events.xml
    │   └── sales.xml
    │
    └── registration.php
```

---

# Database Table Schemas

The module uses two primary ledger tables to maintain a complete transaction history for loyalty coins and store wallet balances.

## 1. `codilar_loyalty_ledger`

This table records all loyalty coin transactions, including:

* Coin earnings
* Coin redemptions
* Coin-to-wallet conversions
* Coin expiration
* Compensating transactions

### SQL Schema


### Important Columns

| Column               | Description                                   |
| -------------------- | --------------------------------------------- |
| `entity_id`          | Unique ledger transaction ID                  |
| `customer_id`        | Customer associated with the transaction      |
| `order_increment_id` | Related Magento order increment ID            |
| `quantity`           | Number of coins added or deducted             |
| `balance_after`      | Customer's coin balance after the transaction |
| `comment`            | Description/reason for the transaction        |
| `created_at`         | Transaction creation timestamp                |

---

## 2. `codilar_store_wallet_ledger`

This table maintains the customer's Store Cash Wallet transaction history.

It records:

* Wallet credits
* Wallet debits
* Loyalty coin conversions
* Checkout wallet spending
* Compensating transactions


### Important Columns

| Column               | Description                              |
| -------------------- | ---------------------------------------- |
| `entity_id`          | Unique wallet transaction ID             |
| `customer_id`        | Customer associated with the transaction |
| `order_increment_id` | Related Magento order increment ID       |
| `amount`             | Wallet amount credited or debited        |
| `balance_after`      | Wallet balance after the transaction     |
| `comment`            | Description/reason for the transaction   |
| `created_at`         | Transaction creation timestamp           |

---

# Key Features

## 1. Loyalty Coins

The module provides a loyalty coin system where customers can earn and spend coins.

Supported operations include:

* Earn loyalty coins
* Redeem loyalty coins
* Convert coins into Store Cash
* Automatically expire unused coins
* Maintain a complete transaction history

---

## 2. Store Cash Wallet

Customers have a separate Store Cash Wallet that can be used during checkout.

The wallet supports:

* Wallet credits
* Wallet deductions
* Loyalty coin conversion
* Checkout spending
* Balance tracking
* Transaction history

---

# 3. Concurrency & Race-Condition Protection

### Pessimistic Row-Level Locking

The wallet redemption process uses database transactions and row-level locking to prevent multiple simultaneous requests from spending the same balance.

The basic transaction flow is:

```text
BEGIN TRANSACTION
        │
        ▼
Lock relevant ledger/balance rows
        │
        ▼
Read current balance
        │
        ▼
Validate requested amount
        │
        ▼
Create deduction ledger entry
        │
        ▼
COMMIT TRANSACTION
```

The locking mechanism prevents situations such as:

```text
Customer Wallet Balance = ₹500

Request A → Spend ₹400
Request B → Spend ₹400
```

Without proper locking, both requests could potentially read the same ₹500 balance.

With transactional locking, one request obtains the lock first and the second request waits until the first transaction completes.

This protects against:

* Double spending
* Incorrect wallet balances
* Concurrent checkout conflicts
* Race conditions

---

# 4. Append-Only Ledger & Immutability

The loyalty and wallet ledgers are designed to be **append-only**.

Existing financial transaction records should not be modified or deleted.

The model includes guardrails such as:

```php
beforeSave()
```

and

```php
beforeDelete()
```

to prevent unauthorized modification or deletion of historical ledger records.

### Example

Instead of modifying an existing transaction:

```text
Existing transaction
+100 coins
```

the system creates a new compensating transaction:

```text
Original transaction
+100 coins

Compensating transaction
-100 coins
```

This preserves the complete transaction history.

---

# 5. Compensating Entries

The system uses compensating entries to correct previous transactions.

For example:

```text
Transaction 1
+500 coins
```

If the transaction needs to be reversed:

```text
Transaction 1
+500 coins

Transaction 2
-500 coins
```

The original transaction remains unchanged.

This provides:

* Full auditability
* Transaction traceability
* Historical integrity
* Easier debugging
* Safer financial reconciliation

---

# 6. FIFO Loyalty Point Expiration

The module includes an automated cron job:

```text
Cron/ExpirePoints.php
```

Unused loyalty coins can automatically expire after the configured validity period.

The expiration logic follows **FIFO (First-In, First-Out)** principles.

### Example

A customer earns:

```text
January
+100 coins

March
+200 coins

June
+300 coins
```

When coins need to be expired or consumed, the oldest eligible coins are processed first.

```text
January coins → processed first
March coins   → processed second
June coins    → processed last
```

The expiration transaction is recorded as a new ledger entry rather than modifying the original earning transaction.

---

# 7. Checkout Wallet Integration

The module integrates the Store Cash Wallet into Magento checkout.

The wallet total collector is located at:

```text
Model/Total/Wallet.php
```

It calculates the wallet amount that should be deducted from the order total.

The checkout flow can be represented as:

```text
Customer Cart
     │
     ▼
Wallet Balance Check
     │
     ▼
Customer Applies Wallet
     │
     ▼
Quote Wallet Amount
     │
     ▼
Order Placement
     │
     ▼
Wallet Deduction
     │
     ▼
Ledger Entry Created
```

---

# 8. Frontend Blocks

The module provides frontend blocks for displaying loyalty and wallet information.

### Customer Dashboard

```text
Block/Customer/Coins.php
Block/Customer/StoreWallet.php
```

These blocks provide customer-facing loyalty coin and wallet information.

### Checkout

```text
Block/Checkout/SuccessPoints.php
Block/Checkout/WalletApply.php
```

These blocks support checkout and order-success functionality.

---

# 9. Controllers

The module contains controllers for customer wallet and loyalty operations.

### Loyalty Coins

```text
Controller/Coins/Index.php
```

Used for the loyalty coins dashboard.

### Wallet

```text
Controller/Wallet/Index.php
Controller/Wallet/AjaxApply.php
Controller/Wallet/Convert.php
```

These controllers handle wallet-related frontend operations such as:

* Viewing wallet information
* Applying wallet balance
* AJAX wallet operations
* Converting loyalty coins into Store Cash

---

# 10. Order Observers

The module uses Magento observers to integrate loyalty and wallet functionality with order events.

```text
Observer/
├── AwardPointsOnOrderPlace.php
├── CopyWalletAmountToOrder.php
├── DeductWalletOnOrderPlace.php
├── ReverseLoyaltyPointsOnCancel.php
└── TransferWalletAmountToOrder.php
```

### Responsibilities

#### `AwardPointsOnOrderPlace.php`

Awards loyalty coins when an eligible order is placed.

#### `CopyWalletAmountToOrder.php`

Transfers wallet-related information from the quote to the order.

#### `DeductWalletOnOrderPlace.php`

Deducts the applied wallet amount from the customer's Store Cash balance.

#### `ReverseLoyaltyPointsOnCancel.php`

Creates compensating loyalty transactions when eligible orders are cancelled.

#### `TransferWalletAmountToOrder.php`

Transfers the applied wallet amount into the order-related data.

---

# 11. Cron Configuration

The module contains:

```text
etc/crontab.xml
```

and:

```text
Cron/ExpirePoints.php
```

The cron process is responsible for identifying and expiring eligible loyalty coins.

Conceptually:

```text
Magento Cron
     │
     ▼
ExpirePoints.php
     │
     ▼
Find expired loyalty transactions
     │
     ▼
Calculate eligible coins
     │
     ▼
Create expiration ledger entries
     │
     ▼
Updated customer balance
```

---

# 12. Magento Configuration

The module uses Magento XML configuration files.

### `etc/di.xml`

Defines dependency injection configuration and service implementations.

### `etc/events.xml`

Registers Magento observers.

### `etc/sales.xml`

Configures sales/cart total behavior and sorting.

### `etc/crontab.xml`

Registers scheduled cron jobs.

### `etc/config.xml`

Contains default module configuration.

### `etc/db_schema.xml`

Defines the module's database tables using Magento declarative schema.

---

# Architecture Overview

The module follows a layered Magento architecture:

```text
                    Customer
                       │
                       ▼
                Frontend Controllers
                       │
                       ▼
                  Service Layer
                       │
             ┌─────────┴─────────┐
             ▼                   ▼
      Loyalty Ledger       Wallet Ledger
             │                   │
             └─────────┬─────────┘
                       ▼
                    MySQL
```

Checkout integration works through:

```text
Cart
 │
 ▼
Total Collector
 │
 ▼
Quote
 │
 ▼
Order Placement
 │
 ├──────────────► Loyalty Observer
 │
 └──────────────► Wallet Observer
                       │
                       ▼
                Transaction Service
                       │
                       ▼
                  Ledger Entry
```

---

# Transaction Safety

The module is designed around the following principles:

| Principle                  | Implementation                           |
| -------------------------- | ---------------------------------------- |
| Ledger immutability        | Existing ledger rows are not modified    |
| Auditability               | Every transaction creates a ledger entry |
| Corrections                | Compensating entries                     |
| Concurrency control        | Database transactions + row locking      |
| Double-spending prevention | Locked balance validation                |
| Point expiration           | Automated cron                           |
| FIFO processing            | Oldest eligible points processed first   |
| Checkout integration       | Magento totals + order observers         |

---

# Feature Summary

| Feature                     | Status        |
| --------------------------- | ------------- |
| Loyalty Coins               | ✅ Implemented |
| Store Cash Wallet           | ✅ Implemented |
| Loyalty Ledger              | ✅ Implemented |
| Wallet Ledger               | ✅ Implemented |
| Append-only transactions    | ✅ Implemented |
| Compensating entries        | ✅ Implemented |
| Pessimistic locking         | ✅ Implemented |
| Race-condition protection   | ✅ Implemented |
| FIFO point expiration       | ✅ Implemented |
| Cron-based expiration       | ✅ Implemented |
| Wallet checkout application | ✅ Implemented |
| Loyalty coin conversion     | ✅ Implemented |
| Order observers             | ✅ Implemented |
| Customer wallet dashboard   | ✅ Implemented |
| Customer loyalty dashboard  | ✅ Implemented |

---

# Installation

Place the module inside:

```text
app/code/Codilar/LoyaltyWallet
```

Then run the Magento commands:

```bash
php bin/magento module:enable Codilar_LoyaltyWallet
php bin/magento setup:upgrade
php bin/magento cache:flush
```

For production mode, also run:

```bash
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

---

