# README.md: Custom Related Products Widget for Magento 2 PDP

A modular,custom Magento 2 widget designed to render  **Related Products** dynamically on the Product Detail Page (PDP). This custom widget bypasses default limitations by allowing dynamic context injection from the current registry product,.

---

## **Technical Specifications**

* **Platform Compatibility:** Magento 2.3.x, 2.4.x
* **Module Namespace:** `Codilar_ProductSlider`
* **Widget Type:** `Codilar\ProductSlider\Block\Widget\RelatedProducts`
* **Dependencies:** `Magento_Catalog`, `Magento_Widget`, `Magento_CatalogRule`

---

## **Project Directory Structure**

```text
app/code/Codilar/ProductSlider/
├── Block/
│   └── Widget/
│       └── RelatedProducts.php
├── etc/
│   ├── module.xml
│   └── widget.xml
├── view/
│   └── frontend/
│       ├── layouts.xml
│       └── templates/
│           └── widget/
│               └── related_products.phtml
├── Readme.md
└── registration.php
