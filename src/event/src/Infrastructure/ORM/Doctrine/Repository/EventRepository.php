<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Infrastructure\ORM\Doctrine\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;

final class EventRepository implements EventRepositoryInterface
{
    /**
     * @var EntityRepository<EventInterface>
     */
    private EntityRepository $entityRepository;

    /**
     * @var EntityRepository<EventTranslationInterface>
     */
    private EntityRepository $entityTranslationRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->entityRepository = $this->entityManager->getRepository(EventInterface::class);
        $this->entityTranslationRepository = $this->entityManager->getRepository(EventTranslationInterface::class);
    }

    public function create(string $defaultLocale): EventInterface
    {
        /** @var class-string<EventInterface> $className */
        $className = $this->entityRepository->getClassName();

        return new $className($defaultLocale, new ArrayCollection());
    }

    public function createTranslation(EventInterface $event, string $locale): EventTranslationInterface
    {
        /** @var class-string<EventTranslationInterface> $className */
        $className = $this->entityTranslationRepository->getClassName();

        return new $className($event, $locale);
    }

    public function add(EventInterface $event): void
    {
        $this->entityManager->persist($event);
    }

    public function remove(EventInterface $event): void
    {
        $this->entityManager->remove($event);
    }

    public function getOneBy(array $filters): EventInterface
    {
        $queryBuilder = $this->createQueryBuilder($filters);

        try {
            $event = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new EventNotFoundException($filters, 0, $e);
        }

        return $event;
    }

    public function findOneBy(array $filters): ?EventInterface
    {
        $queryBuilder = $this->createQueryBuilder($filters);

        try {
            $event = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }

        return $event;
    }

    public function findFlat(array $filters = [], array $sortBys = []): iterable
    {
        $queryBuilder = $this->createQueryBuilder($filters, $sortBys);
        $queryBuilder->select('event.id')
            ->addSelect('event.defaultLocale');

        $locale = $filters['locale'] ?? null;
        if ($locale) {
            $queryBuilder->addSelect('localeEventTranslation.title');
        } else {
            if (!isset($sortBys['title'])) {
                $this->addDefaultTranslationJoin($queryBuilder);
            }

            $queryBuilder->addSelect('defaultEventTranslation.title');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findBy(array $filters = [], array $sortBys = []): iterable
    {
        $queryBuilder = $this->createQueryBuilder($filters, $sortBys);

        return $queryBuilder->getQuery()->toIterable();
    }

    public function countBy(array $filters = []): int
    {
        $queryBuilder = $this->createQueryBuilder($filters);
        $queryBuilder->select('COUNT(event)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string,
     *     page?: int,
     *     limit?: int,
     * } $filters
     * @param array<'id'|'key'|'title', 'asc'|'desc'> $sortBys
     */
    private function createQueryBuilder(array $filters, array $sortBys = []): QueryBuilder
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

        $id = $filters['id'] ?? null;
        if ($id) {
            $queryBuilder->andWhere('event.id = :id')
                ->setParameter('id', $id);
        }

        $ids = $filters['ids'] ?? null;
        if ($ids) {
            $queryBuilder->andWhere('event.id IN(:ids)')
                ->setParameter('ids', $ids);
        }

        $locale = $filters['locale'] ?? null;
        if ($locale) {
            $this->addLocaleTranslationJoin($queryBuilder, $locale);
        }

        $limit = $filters['limit'] ?? null;
        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        $page = $filters['page'] ?? null;
        if ($page) {
            $offset = ($page * $limit) - $limit;
            $queryBuilder->setFirstResult($offset);
        }

        foreach ($sortBys as $field => $ascDesc) {
            if ('id' === $field) {
                $queryBuilder->addOrderBy('event.id', $ascDesc);
            } elseif ('title' === $field) {
                $locale = $filters['locale'] ?? null;
                if ($locale) {
                    $queryBuilder->addOrderBy('localeEventTranslation.title', $ascDesc);
                } else {
                    $this->addDefaultTranslationJoin($queryBuilder);
                    $queryBuilder->addOrderBy('defaultEventTranslation.title', $ascDesc);
                }
            }
        }

        return $queryBuilder;
    }

    private function addDefaultTranslationJoin(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->innerJoin(
            EventTranslationInterface::class,
            'defaultEventTranslation',
            Join::WITH,
            'defaultEventTranslation.event = event AND defaultEventTranslation.locale = event.defaultLocale'
        );
    }

    private function addLocaleTranslationJoin(QueryBuilder $queryBuilder, string $locale): void
    {
        $queryBuilder->innerJoin(
            EventTranslationInterface::class,
            'localeEventTranslation',
            Join::WITH,
            'localeEventTranslation.event = event AND localeEventTranslation.locale = :locale'
        )->setParameter('locale', $locale);
    }
}
