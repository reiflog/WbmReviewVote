<?php

namespace WbmReviewVote\Controller\StoreApi;

use Doctrine\DBAL\Exception;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityNotExists;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use WbmReviewVote\Content\ReviewVote\ReviewVoteEntity;
use WbmReviewVote\Exception\ReviewVoteInactiveException;
use WbmReviewVote\Service\ReviewVoteService;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class ReviewVoteSaveRoute extends AbstractReviewVoteSaveRoute
{
    public function __construct(
        private EntityRepository $reviewVoteRepository,
        private DataValidator $validator,
        private EntityRepository $productReviewRepository,
        private ReviewVoteService $reviewVoteService,
        private SystemConfigService $configService
    )
    {
    }

    public function getDecorated(): AbstractReviewVoteSaveRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @throws Exception
     * @throws ReviewVoteInactiveException
     */
    #[Route(path: '/store-api/review-vote/{productReviewId}', name: 'store-api.review-vote.save', defaults: ['_loginRequired' => true], methods: ['POST'])]
    public function save(string $productReviewId, RequestDataBag $data, SalesChannelContext $context): JsonResponse
    {
        $this->checkReviewVoteActive($context);

        $customerId = $context->getCustomer()->getId();
        $data->set('productReviewId', $productReviewId);
        $data->set('customerId', $customerId);

        $this->validate($data, $context);

        //Search for current vote of customer for this review
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('productReviewId', $productReviewId),
            new EqualsFilter('customerId', $customerId),
        );

        /** @var ReviewVoteEntity $customerReviewVote */
        $customerReviewVote = $this->reviewVoteRepository->search($criteria, $context->getContext())->first();

        if(!$customerReviewVote) {
            $this->createReview($data, $context);
        } else {
            $data->set('id', $customerReviewVote->getId());

            if($customerReviewVote->getType() === $data->get('type')) {
                $this->deleteReview($data, $context);
            } else {
                $this->updateReview($data, $context);
            }
        }

        $votesOfReview = $this->reviewVoteService->fetchVotesOfReviews([$productReviewId], $customerId);

        $response = ReviewVoteService::format(
            $votesOfReview[strtoupper($productReviewId)]["upvotes"] ?? 0,
            $votesOfReview[strtoupper($productReviewId)]["downvotes"] ?? 0,
                $votesOfReview[strtoupper($productReviewId)]["customer_vote_type"] ?? null
        );

        return new JsonResponse(
            $response
        );
    }

    private function deleteReview(DataBag $dataBag, SalesChannelContext $context): void
    {
        $this->reviewVoteRepository->delete(
            [
                [
                    'id' => $dataBag->get("id")
                ]
            ],
            $context->getContext()
        );
    }

    private function updateReview(DataBag $dataBag, SalesChannelContext $context): void
    {
        $this->reviewVoteRepository->upsert(
            [
                [
                    'id' => $dataBag->get("id"),
                    'customerId' => $dataBag->get("customerId"),
                    'productReviewId' => $dataBag->get("productReviewId"),
                    'type' => $dataBag->get("type")
                ]
            ],
            $context->getContext()
        );
    }

    private function createReview(DataBag $dataBag, SalesChannelContext $context): void
    {
        $this->reviewVoteRepository->create(
            [
                [
                    'id' => Uuid::randomHex(),
                    'type' => $dataBag->get("type"),
                    'customerId' => $dataBag->get("customerId"),
                    'productReviewId' => $dataBag->get("productReviewId"),
                ]
            ],
            $context->getContext()
        );
    }

    private function validate(DataBag $data, SalesChannelContext $context): void
    {
        $definition = new DataValidationDefinition('reviewvote.create_vote');
        $definition->add('productReviewId', new NotBlank());
        $definition->add('customerId', new NotBlank());
        $definition->add('type', new Choice([ReviewVoteEntity::TYPE_DOWNVOTE, ReviewVoteEntity::TYPE_UPVOTE]));

        //Check if Config "Don't vote on own reviews" if active
        if($this->configService->getBool('WbmReviewVote.config.voteOwnReview', $context->getSalesChannelId())) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('id', $data->get('productReviewId')));
            $criteria->addFilter(new EqualsFilter('customerId', $data->get('customerId')));

            $definition->add('customerId', new EntityNotExists([
                "entity" => ProductReviewDefinition::ENTITY_NAME,
                "context" => $context->getContext(),
                "criteria" => $criteria
            ]));
        }

        $this->validator->validate($data->all(), $definition);

        $violations = $this->validator->getViolations($data->all(), $definition);

        if (!$violations->count()) {
            return;
        }

        throw new ConstraintViolationException($violations, $data->all());
    }

    /**
     * @throws ReviewVoteInactiveException
     */
    private function checkReviewVoteActive(SalesChannelContext $context): void
    {
        //todo
        //Check if Config "enabled for this SalesChannel" in the Plugin Config is active
        if(!$this->configService->getBool('WbmReviewVote.config.active', $context->getSalesChannelId())) {
            throw new ReviewVoteInactiveException();
        }
    }
}