<?php

namespace WbmReviewVote\Content\ProductReview;

use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use WbmReviewVote\Content\ReviewVote\ReviewVoteDefinition;

class ProductReviewExtension extends EntityExtension
{

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('reviewVotes', ReviewVoteDefinition::class, 'product_review_id')
        );
    }
    public function getDefinitionClass(): string
    {
        return ProductReviewDefinition::class;
    }
}