<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Api;

use Codilar\ProductEnquiry\Api\Data\EnquiryInterface;

interface EnquiryRepositoryInterface
{
    /**
     * @param \Codilar\ProductEnquiry\Api\Data\EnquiryInterface $enquiry
     * @return \Codilar\ProductEnquiry\Api\Data\EnquiryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(EnquiryInterface $enquiry): EnquiryInterface;

}
