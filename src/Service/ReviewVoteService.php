<?php

namespace WbmReviewVote\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class ReviewVoteService
{
    public function __construct(private Connection $connection)
    {
    }

    public static function format(int $upvotes = 0, int $downvotes = 0, string $customerVoteType = null): array
    {
        return [
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
            "customerVoteType" => $customerVoteType
        ];
    }

    /**
     * @param array $productReviewIds
     * @param string|null $customerId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function fetchVotesOfReviews(array $productReviewIds, ?string $customerId = ""): array
    {
        // Get reviewVotes for fetched reviews
        $query = "
        SELECT 
    HEX(product_review_id) as product_review_id,
    SUM(CASE WHEN type = 'upvote' THEN 1 ELSE 0 END) AS upvotes,
    SUM(CASE WHEN type = 'downvote' THEN 1 ELSE 0 END) AS downvotes,
    MAX(CASE WHEN customer_id = UNHEX(?) THEN type ELSE NULL END) AS customer_vote_type
FROM 
    wbm_review_vote
WHERE 
    HEX(product_review_id) IN (?)
GROUP BY 
    product_review_id;";

        return $this->connection->fetchAllAssociativeIndexed(
            $query,
            [$customerId, $productReviewIds],
            [ParameterType::STRING, Connection::PARAM_STR_ARRAY]
        );

    }
}