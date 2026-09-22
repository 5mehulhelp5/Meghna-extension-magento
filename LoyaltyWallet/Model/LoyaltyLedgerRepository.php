<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model;

use Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterface;
use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Codilar\LoyaltyWallet\Model\ResourceModel\LoyaltyLedger as ResourceLoyaltyLedger;
use Codilar\LoyaltyWallet\Model\ResourceModel\LoyaltyLedger\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class LoyaltyLedgerRepository implements LoyaltyLedgerRepositoryInterface
{
    protected ResourceLoyaltyLedger $resource;
    protected CollectionFactory $collectionFactory;
    protected SearchResultsInterfaceFactory $searchResultsFactory;

    public function __construct(
        ResourceLoyaltyLedger $resource,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param LoyaltyLedgerInterface $ledger
     * @return LoyaltyLedgerInterface
     * @throws CouldNotSaveException
     */
    public function save(LoyaltyLedgerInterface $ledger): LoyaltyLedgerInterface
    {
        try {
            $this->resource->save($ledger);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $ledger;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();

        // Apply filters from search criteria
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        // Apply sort orders
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() === \Magento\Framework\Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }

        // Apply pagination (Page Size & Current Page)
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
