# Codilar Store Location Module for Magento 2

A robust, enterprise-grade Magento 2 extension designed to manage, process, and display physical store locations. Built following strict Magento architectural standards—including Service Contracts, REST API compliance, standardized JSON response envelopes, type-safe controllers, custom blocks, resource collections, and secure relative media asset handling.

---

## Key Features

* **RESTful Service Contracts:** Fully compliant API endpoints for complete CRUD operations (`POST`, `GET`, `DELETE`) backed by data and service interfaces.
* **Standardized Response Envelope:** Uniform JSON structure across all API responses containing `status`, `code`, `message`, and `data` payloads.
* **Base64 Image Processing:** Intelligent base64 validation, metadata parsing, unique file generation, and secure storage inside the `pub/media/store_locations/` directory.
* **Admin ACL Controls:** Fine-grained administrative permission rules defined via `acl.xml` for secure store management.
* **Frontend Presentation:** Integrated frontend controllers, blocks, and `.phtml` templates for rendering interactive store locators and submission forms.

---



## Complete Module Folder Structure

```text
app/code/Codilar/StoreLocation/
├── Api/                                # Public Service Contracts & Data Interfaces
│   ├── Data/
│   │   ├── StoreInterface.php
│   │   └── StoreApiResponseInterface.php
│   └── StoreRepositoryInterface.php
├── Block/                              # View Presentation & Template Logic
│   └── StoreList.php
├── Controller/                         # Request Handlers & Action Logic
│   └── Index/
│       ├── Form.php                    # Renders the store submission form
│       ├── Index.php                   # Renders the main store locator listing page
│       ├── Save.php                    # Handles persistence and image uploads
│       └── Success.php                 # Renders the post-submission success page
├── Model/                              # Business Logic, Entities, & Database Operations
│   ├── ResourceModel/
│   │   ├── Store/
│   │   │   ├── Collection.php
│   │   │   └── CollectionFactory.php
│   │   └── Store.php
│   ├── Store.php
│   ├── StoreApiResponse.php
│   └── StoreRepository.php
├── etc/                                # Module Configuration & Routing XMLs
│   ├── acl.xml                         # Admin access control list rules
│   ├── module.xml                      # Module declaration
│   ├── routes.xml                      # Frontend router configuration
│   └── webapi.xml                      # REST API route mappings
├── view/                               # Frontend Layouts, Styles, and Templates
│   └── frontend/
│       ├── layout/
│       │   ├── stores_index_form.xml
│       │   ├── stores_index_index.xml
│       │   └── stores_index_success.xml
│       └── templates/
│           ├── form.phtml
│           └── storelist.phtml
└── composer.json                       # Module Dependency Declaration


REST API Endpoints Reference

All API responses return a standard envelope structure:

```json
{
    "status": "success",
    "code": 200,
    "message": "Store locations fetched successfully.",
    "data": [...]
}
