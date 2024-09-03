<?php

namespace WbmReviewVote\Content\ReviewVote;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ReviewVoteEntity extends Entity
{
    use EntityIdTrait;

    const TYPE_UPVOTE = "upvote";
    const TYPE_DOWNVOTE = "downvote";

    protected string $type = self::TYPE_UPVOTE;
    protected CustomerEntity $customer;
    protected ProductReviewEntity $productReview;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }


    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getProductReview(): ProductReviewEntity
    {
        return $this->productReview;
    }

    public function setProductReview(ProductReviewEntity $productReview): void
    {
        $this->productReview = $productReview;
    }
}