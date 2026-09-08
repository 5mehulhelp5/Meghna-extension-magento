# Codilar Store Location Module for Magento 2

A robust, custom Magento 2 extension designed to manage, process, and display physical store locations. Built following strict Magento architectural standards—including Service Contracts, type-safe controllers (`PageFactory`), custom blocks, resource collections, and secure relative media asset handling.

---

##  Complete Module Folder Structure

```text
app/code/Codilar/StoreLocation/
├── Api/                          # Public Service Contracts & Data Interfaces
│   ├── Data/
│   │   └── StoreInterface.php
│   └── StoreRepositoryInterface.php
├── Block/                        # View Presentation & Template Logic
│   └── StoreList.php
├── Controller/                   # Request Handlers & Action Logic
│   └── Index/
│       ├── Form.php              # Renders the store submission form
│       ├── Index.php             # Renders the main store locator listing page
│       ├── Save.php              # Handles persistence and image uploads
│       └── Success.php           # Renders the post-submission success page
├── Model/                        # Business Logic, Entities, & Database Operations
│   ├── ResourceModel/
│   │   └── Store/
│   │       ├── Collection.php
│   │       └── CollectionFactory.php
│   ├── Store.php
│   └── StoreRepository.php
├── etc/                          # Module Configuration & Routing XMLs
│   ├── module.xml
│   └── routes.xml
├── view/                         # Frontend Layouts, Styles, and Templates
│   └── frontend/
│       ├── layout/
│       │   ├── stores_index_form.xml
│       │   ├── stores_index_index.xml
│       │   └── stores_index_success.xml
│       └── templates/
│           ├── form.phtml
│           └── storelist.phtml
└── composer.json                 # Module Dependency Declaration
