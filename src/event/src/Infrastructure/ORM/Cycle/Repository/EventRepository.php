<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Infrastructure\ORM\Cycle\Repository;

use Cycle\ORM\ORM;
use Cycle\ORM\Schema;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Cycle\ORM\TransactionInterface;
use Doctrine\Common\Collections\ArrayCollection;
use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Model\Event;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;
use Spiral\Database\Injection\Expression;
use Spiral\Database\Injection\Parameter;
use Spiral\Database\Query\SelectQuery;

class EventRepository implements EventRepositoryInterface
{
    private Repository $repository;

    public function __construct(
        private ORM $orm,
        private TransactionInterface $transaction
    ) {
        $this->repository = $this->orm->getRepository(Event::class);
    }

    public function create(string $defaultLocale): EventInterface
    {
        /** @var class-string<EventInterface> $className */
        $className = $this->orm->getSchema()->define('event', Schema::ENTITY);

        return new $className($defaultLocale, new ArrayCollection());
    }

    public function createTranslation(EventInterface $event, string $locale): EventTranslationInterface
    {
        /** @var class-string<EventTranslationInterface> $className */
        $className = $this->orm->getSchema()->define('event_translation', Schema::ENTITY);

        return new $className($event, $locale);
    }

    public function add(EventInterface $event): void
    {
        $this->transaction->persist($event);
    }

    public function remove(EventInterface $event): void
    {
        $this->transaction->delete($event);
    }

    public function getOneBy(array $filters): EventInterface
    {
        $select = $this->createSelect($filters);

        /** @var EventInterface|null $event */
        $event = $select->fetchOne();

        if (!$event) {
            throw new EventNotFoundException($filters);
        }

        return $event;
    }

    public function findOneBy(array $filters): ?EventInterface
    {
        $select = $this->createSelect($filters);

        /** @var EventInterface|null $event */
        $event = $select->fetchOne();

        return $event;
    }

    public function findFlat(array $filters = [], array $sortBys = []): iterable
    {
        $select = $this->createSelect($filters, $sortBys);

        $columns = [
            'event.id',
            'event.defaultLocale',
        ];

        $locale = $filters['locale'] ?? null;
        if ($locale) {
            $columns[] = 'localeEventTranslation.title';
        } else {
            if (!isset($sortBys['title'])) {
                $this->addDefaultTranslationJoin($select);
            }
            $columns[] = 'defaultEventTranslation.title';
        }

        $rawSelect = $select
            ->buildQuery();

        $rawSelect = $rawSelect->columns($columns);

        foreach ($rawSelect->getIterator() as $row) {
            $row['id'] = (int) $row['id'];

            yield $row;
        }
    }

    public function findBy(array $filters = [], array $sortBys = []): iterable
    {
        $select = $this->createSelect($filters, $sortBys);

        return $select->getIterator();
    }

    public function countBy(array $filters = []): int
    {
        $select = $this->createSelect($filters);

        return $select->count();
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
    private function createSelect(array $filters, array $sortBys = []): Select
    {
        $select = $this->repository->select();

        $id = $filters['id'] ?? null;
        if ($id) {
            $select->andWhere('id', new Parameter($id));
        }

        $ids = $filters['ids'] ?? null;
        if ($ids) {
            $select->andWhere([
                'id' => ['in' => new Parameter($ids)],
            ]);
        }

        $locale = $filters['locale'] ?? null;
        if ($locale) {
            $this->addLocaleTranslationJoin($select, $locale);
        }

        $limit = $filters['limit'] ?? null;
        if ($limit) {
            $select->limit($limit);
        }

        $page = $filters['page'] ?? null;
        if ($page) {
            $offset = ($page * $limit) - $limit;
            $select->offset($offset);
        }

        foreach ($sortBys as $field => $ascDesc) {
            $direction = 'asc' === $ascDesc ? SelectQuery::SORT_ASC : SelectQuery::SORT_DESC;

            if ('id' === $field) {
                $select->orderBy('event.id', $direction);
            } elseif ('title' === $field) {
                $locale = $filters['locale'] ?? null;
                if ($locale) {
                    $select->orderBy('localeEventTranslation.title', $direction);
                } else {
                    $this->addDefaultTranslationJoin($select);
                    $select->orderBy('defaultEventTranslation.title', $direction);
                }
            }
        }

        return $select;
    }

    private function addDefaultTranslationJoin(Select $select): void
    {
        $select->innerJoin(new Expression('fcp_event_translation'), 'defaultEventTranslation')
            ->on(new Expression('defaultEventTranslation.event_id'), new Expression('defaultEventTranslation.id'))
            ->andOn(new Expression('defaultEventTranslation.locale'), new Expression('event.defaultLocale'));
    }

    private function addLocaleTranslationJoin(Select $select, mixed $locale)
    {
        $select->innerJoin(new Expression('fcp_event_translation'), 'localeEventTranslation')
            ->on(new Expression('localeEventTranslation.event_id'), new Expression('event.id'))
            ->andOn(new Expression('localeEventTranslation.locale'), new Parameter($locale));
    }
}
