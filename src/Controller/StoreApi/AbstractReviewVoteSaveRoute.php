<?php

namespace WbmReviewVote\Controller\StoreApi;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractReviewVoteSaveRoute
{
    abstract public function getDecorated(): AbstractReviewVoteSaveRoute;

    abstract public function save(string $productReviewId, RequestDataBag $data, SalesChannelContext $context): JsonResponse;
}