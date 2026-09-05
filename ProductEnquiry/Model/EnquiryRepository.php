<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Model;

use Codilar\ProductEnquiry\Api\Data\EnquiryInterface;
use Codilar\ProductEnquiry\Api\Data\EnquiryInterfaceFactory;
use Codilar\ProductEnquiry\Api\EnquiryRepositoryInterface;
use Codilar\ProductEnquiry\Model\ResourceModel\Enquiry as EnquiryResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class EnquiryRepository implements EnquiryRepositoryInterface
{
    public function __construct(
        private readonly EnquiryResource $resource,
        private readonly EnquiryInterfaceFactory $enquiryFactory
    ) {}

    public function save(EnquiryInterface $enquiry): EnquiryInterface
    {
        try {
            $this->resource->save($enquiry);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Could not save product enquiry: %1', $e->getMessage()), $e);
        }
        return $enquiry;
    }


}
