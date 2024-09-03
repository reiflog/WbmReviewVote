<?php

namespace WbmReviewVote\Subscriber;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchResultLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductReviewSubscriber implements EventSubscriberInterface
{
    public function __construct(protected Connection $connection)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            'product_review.search.result.loaded' => "onResultLoaded"
        ];
    }

    /**
     * @throws Exception
     */
    public function onResultLoaded(EntitySearchResultLoadedEvent $event)
    {
        $productReviewIds = $event->getResult()->getIds();

        $query = "
        SELECT 
    product_review_id,
    SUM(CASE WHEN type = 'upvote' THEN 1 ELSE 0 END) AS upvotes,
    SUM(CASE WHEN type = 'downvote' THEN 1 ELSE 0 END) AS downvotes,
    MAX(CASE WHEN customer_id = UNHEX('?') THEN type ELSE NULL END) AS customer_vote_type
FROM 
    wbm_review_vote
WHERE
    HEX(id) IN (?)
GROUP BY 
    product_review_id;";

        $this->connection->executeStatement($query, [implode(',', $productReviewIds)]);
    }
}