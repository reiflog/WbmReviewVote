<?php

namespace WbmReviewVote\Core\Content\Product\SalesChannel\Review;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;
use Shopware\Core\Content\Product\SalesChannel\Review\AbstractProductReviewRoute;
use Shopware\Core\Content\Product\SalesChannel\Review\ProductReviewRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use WbmReviewVote\Service\ReviewVoteService;

class ProductReviewRoute extends AbstractProductReviewRoute
{

    public function __construct(
        protected AbstractProductReviewRoute $inner,
        protected ReviewVoteService $reviewVoteService
    )
    {}

    public function getDecorated(): AbstractProductReviewRoute
    {
        return $this->inner;
    }


    /**
     * @throws Exception
     */
    public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductReviewRouteResponse
    {
        $criteria->addAssociation('reviewVotes');

        $response = $this->inner->load($productId, $request, $context, $criteria);

        $result = $response->getResult();

        $voteResult = $this->reviewVoteService->fetchVotesOfReviews($result->getIds(), $context->getCustomerId());
        $result = $this->addVoteReviewExtension($result, $voteResult);

        return new ProductReviewRouteResponse($result);
    }

    protected function addVoteReviewExtension(EntitySearchResult $result, array $voteResult): EntitySearchResult
    {
        //Loop through Reviews and add Vote Information as Extension
        /**
         * @var ProductReviewEntity $productReview
         */
        foreach ($result->getElements() as &$productReview) {
            $upvotes = 0;
            $downvotes = 0;
            $customerVoteType = null;

            $arrayIndex = strtoupper($productReview->getId());

            //If Reviews exists
            if(isset($voteResult[$arrayIndex])) {
                $upvotes = $voteResult[$arrayIndex]["upvotes"];
                $downvotes = $voteResult[$arrayIndex]["downvotes"];
                $customerVoteType = $voteResult[$arrayIndex]["customer_vote_type"];
            }

            $productReview->addArrayExtension(
                'voteCounter',
                ReviewVoteService::format($upvotes, $downvotes, $customerVoteType)
            );
        }
        return $result;
    }
}