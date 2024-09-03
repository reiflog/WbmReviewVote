<?php

namespace WbmReviewVote\Controller\StoreApi;

use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class ReviewVoteSaveRouteResponse  extends StoreApiResponse
{
    /**
     * @var EntitySearchResult<ProductReviewCollection>
     */
    protected $object;

    /**
     * @param EntitySearchResult<ProductReviewCollection> $object
     */
    public function __construct(EntitySearchResult $object)
    {
        parent::__construct($object);
    }

    /**
     * @return EntitySearchResult<ProductReviewCollection>
     */
    public function getResult(): EntitySearchResult
    {
        return $this->object;
    }
}