<?php

namespace WbmReviewVote\Content\ReviewVote;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                     add(ReviewVoteEntity $entity)
 * @method void                     set(string $key, ReviewVoteEntity $entity)
 * @method ReviewVoteEntity[]       getIterator()
 * @method ReviewVoteEntity[]       getElements()
 * @method ReviewVoteEntity|null    get(string $key)
 * @method ReviewVoteEntity|null    first()
 * @method ReviewVoteEntity|null    last()
 */
class ReviewVoteCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ReviewVoteEntity::class;
    }
}