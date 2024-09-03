<?php

namespace WbmReviewVote\Controller\Storefront;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use WbmReviewVote\Controller\StoreApi\AbstractReviewVoteSaveRoute;

#[Route(defaults: ['_routeScope' => ['storefront']])]

class ReviewVoteController extends StorefrontController
{
    public function __construct(protected AbstractReviewVoteSaveRoute $reviewVoteSaveRoute)
    {
    }

    #[Route(path: '/wbm/review-vote/{productReviewId}', name: 'frontend.review-vote.vote', defaults: ['XmlHttpRequest' => true, '_loginRequired' => true], methods: ['POST'])]
    public function voteReview(string $productReviewId, RequestDataBag $data, SalesChannelContext $context): JsonResponse
    {
        try {
            $response = $this->reviewVoteSaveRoute->save($productReviewId, $data, $context);
        } catch (ConstraintViolationException $formViolations) {
            $response = $this->json($formViolations->getCode());
        }

        return $this->json(json_decode($response->getContent()));
    }
}