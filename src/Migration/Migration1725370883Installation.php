<?php declare(strict_types=1);

namespace WbmReviewVote\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1725370883Installation extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1725370883;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {

        $query = '
        CREATE TABLE `wbm_review_vote` (
    `id` BINARY(16) NOT NULL,
    `type` VARCHAR(16) NOT NULL,
    `customer_id` BINARY(16) NOT NULL,
    `product_review_id` BINARY(16) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    KEY `fk.wbm_review_vote.customer_id` (`customer_id`),
    KEY `fk.wbm_review_vote.product_review_id` (`product_review_id`),
    CONSTRAINT `fk.wbm_review_vote.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.wbm_review_vote.product_review_id` FOREIGN KEY (`product_review_id`) REFERENCES `product_review` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';

        $connection->executeStatement($query);
    }
}
