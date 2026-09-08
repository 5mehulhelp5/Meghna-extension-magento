# Codilar Product Enquiry Module for Magento 2

A robust, custom Magento 2 extension designed to replace or complement the native **"Add to Cart"** workflow with an interactive, AJAX-powered product enquiry modal.

Built following strict Magento architectural standards—including **Service Contracts, type-safe controllers (`ResultFactory`), custom blocks, resource collections, UI modal widgets, and secure escaping practices**.

---

## Complete Module Folder Structure

```text
app/code/Codilar/ProductEnquiry/
├── Api/                            # Public Service Contracts & Data Interfaces
│   ├── Data/
│   │   └── EnquiryInterface.php
│   └── EnquiryRepositoryInterface.php
├── Block/                          # View Presentation & Template Logic
│   └── Enquiry/
│       └── History.php             # Prepares and sorts the enquiry collection for the history grid
├── Controller/                     # Request Handlers & Action Logic
│   ├── Enquiry/
│   │   └── Index.php               # Renders the frontend customer enquiry history page
│   └── Index/
│       └── Submit.php              # Handles asynchronous AJAX POST submissions and database saves
├── Logger/                         # Custom Activity & Error Logging
│   └── Logger.php
├── Model/                          # Business Logic, Entities, & Database Operations
│   ├── ResourceModel/
│   │   └── Enquiry/
│   │       ├── Collection.php
│   │       └── Enquiry.php
│   ├── Enquiry.php
│   └── EnquiryRepository.php
├── etc/                            # Module Configuration & Routing XMLs
│   ├── module.xml
│   └── routes.xml
└── view/                           # Frontend Layouts, JavaScript Widgets, and Templates
    └── frontend/
        ├── layout/
        │   └── codilar_productenquiry_enquiry_index.xml
        ├── templates/
        │   └── enquiry/
        │       └── history.phtml
        └── web/
            └── js/
                └── enquiry-modal.js # UI modal widget handling form validation, AJAX, and dynamic SKU detection
```

---

## Database Schema

### `codilar_product_enquiry`

| Column Name  | Data Type         | Description                                       |
| ------------ | ----------------- | ------------------------------------------------- |
| `entity_id`  | Int (Primary Key) | Auto-increment unique identifier for each enquiry |
| `name`       | Varchar           | Customer name                                     |
| `email`      | Varchar           | Customer email address                            |
| `address`    | Text              | Customer physical address or inquiry message      |
| `sku`        | Varchar           | Product SKU dynamically captured from the page    |
| `qty`        | Int               | Desired product quantity                          |
| `created_at` | Timestamp         | Record creation timestamp                         |

---

## Key Features

### AJAX-Powered Modal Widget

Integrates smoothly with Magento's UI modal system (`Magento_Ui/js/modal/modal`), automatically grabbing the product SKU and submitting form data without requiring a full page reload.

### Service Contract Architecture

Strictly decouples business logic using data interfaces, repositories, and dependency injection.

### Custom Database Storage

Stores customer inquiries securely inside a dedicated MySQL table.

### Frontend History Grid

Provides a dedicated storefront route:

```text
codilar_productenquiry/enquiry/index
```

