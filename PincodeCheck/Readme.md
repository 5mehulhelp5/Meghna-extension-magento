# README.md: Codilar_PincodeCheck Module for Magento 2

A modular Magento 2 custom module designed to add an interactive **Delivery Availability Checker** directly on the Product Detail Page (PDP). Customers can input their postal PIN code to verify delivery availability dynamically using AJAX.

---

## **Technical Specifications**

* **Platform Compatibility:** Magento 2.3.x, 2.4.x
* **Module Namespace:** `Codilar_PincodeCheck`
* **Controller Route:** `pincode/index/check`
* **Dependencies:** `Magento_Catalog`, `Magento_Widget`

---

## **Project Directory Structure**

```text
app/code/Codilar/PincodeCheck/
├── Controller/
│   └── Index/
│       └── Check.php          # Handles AJAX validation and returns JSON response
├── etc/
│   └── frontend/
│       └── module.xml         # Module declaration & frontend sequence
├── view/
│   └── frontend/
│       ├── layout/
│       │   └── catalog_product_view.xml # Layout instructions for PDP injection
│       ├── templates/
│       │   └── pincode.phtml  # HTML input form & jQuery AJAX script
│       └── web/               # Static frontend assets (CSS/JS if modularized)
└── registration.php           # Component registrar
```

---

## **Core Implementation Details**

1. **Frontend Template (`pincode.phtml`):** Renders the input box and submit button on the PDP. It captures the form submit event via jQuery, prevents page reload, and sends an AJAX POST request.
2. **AJAX Controller (`Check.php`):** Validates the incoming PIN code against a predefined PHP array list and returns a structured JSON response:
   ```json
   {
     "status": true,
     "message": "✓ Delivery available",
     "estimate": "Expected delivery: 3–5 days"
   }
   ```
3. **Layout XML (`catalog_product_view.xml`):** Injects the pincode block seamlessly into the product info layout container.

---
