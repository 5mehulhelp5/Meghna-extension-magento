<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Block\Adminhtml\Store\Edit;

use Magento\Backend\Block\Widget\Context;

class Generic
{
    protected Context $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function getStoreId()
    {
        return $this->context->getRequest()->getParam('store_id');
    }

    public function getUrl($route = '', $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
