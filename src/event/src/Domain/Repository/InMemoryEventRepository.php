<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Domain\Repository;

use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Model\Event;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslation;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;

class InMemoryEventRepository implements EventRepositoryInterface
{
    /**
     * @var EventInterface
     */
    private $list = [];

    /**
     * @var int
     */
    private $autoincrementIdCounter = 0;

    public function create(string $defaultLocale): EventInterface
    {
        return new Event($defaultLocale);
    }

    public function createTranslation(EventInterface $event, string $locale): EventTranslationInterface
    {
        return new EventTranslation($event, $locale);
    }

    public function add(EventInterface $event): void
    {
        $reflection = new \ReflectionClass($event);
        $propertyReflection = $reflection->getProperty('id');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($event, ++$this->autoincrementIdCounter);

        $this->list[] = $event;
    }

    public function remove(EventInterface $event): void
    {
        foreach ($this->list as $key => $listEvent) {
            if ($event->getId() === $listEvent->getId()) {
                unset($this->list[$key]);
            }
        }
    }

    public function getOneBy(array $filters): EventInterface
    {
        $events = $this->filterEvents($filters);

        if (0 === \count($events)) {
            throw new EventNotFoundException($filters);
        }

        return $events[0];
    }

    public function findOneBy(array $filters): ?EventInterface
    {
        $events = $this->filterEvents($filters);

        if (0 === \count($events)) {
            return null;
        }

        return $events[0];
    }

    public function findFlat(array $filters = [], array $sortBys = []): iterable
    {
        $events = $this->filterEvents($filters, $sortBys);
        foreach ($events as $event) {
            $translation = $event->findTranslation($event->getDefaultLocale());
            $title = $translation ? $translation->getTitle() : '';

            yield [
                'id' => $event->getId(),
                'defaultLocale' => $event->getDefaultLocale(),
                'title' => $title,
            ];
        }
    }

    public function findBy(array $filters = [], array $sortBys = []): iterable
    {
        $events = $this->filterEvents($filters, $sortBys);

        foreach ($events as $event) {
            yield $event;
        }
    }

    public function countBy(array $filters = []): int
    {
        $events = $this->filterEvents($filters);

        return \count($events);
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
     *
     * @return EventInterface[]
     */
    private function filterEvents(array $filters = [], array $sortBys = []): array
    {
        $events = [];
        foreach ($this->list as $event) {
            if ($event = $this->filterEvent($event, $filters)) {
                $events[] = $event;
            }
        }

        if (!empty($sortBys)) {
            usort($events, function (EventInterface $a, EventInterface $b) use ($sortBys) {
                foreach ($sortBys as $field => $ascDesc) {
                    $strcmp = 0;

                    if ('id' === $field) {
                        $strcmp = strcmp((string) $a->getId(), (string) $a->getId());
                    } elseif ('title' === $field) {
                        $aTranslation = $a->getTranslation($locale ?? $a->getDefaultLocale());
                        $bTranslation = $b->getTranslation($locale ?? $b->getDefaultLocale());
                        $strcmp = strcmp($aTranslation->getTitle(), $bTranslation->getTitle());
                    }

                    if (0 !== $strcmp) {
                        return 'desc' === $ascDesc ? -$strcmp : $strcmp;
                    }
                }

                return 0;
            });
        }

        $limit = $filters['limit'] ?? null;
        $page = $filters['page'] ?? null;
        $offset = 0;

        if ($page) {
            $offset = ($page - 1) * $limit;
        }

        if ($limit) {
            $events = array_splice($events, $offset, $limit);
        }

        return $events;
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string,
     * } $filters
     */
    private function filterEvent(EventInterface $event, array $filters = []): ?EventInterface
    {
        $id = $filters['id'] ?? null;
        if ($id && $event->getId() !== $id) {
            return null;
        }

        $ids = $filters['ids'] ?? null;
        if ($ids && !\in_array($event->getId(), $ids, true)) {
            return null;
        }

        $locale = $filters['locale'] ?? null;
        if ($locale && !$event->findTranslation($locale)) {
            return null;
        }

        return $event;
    }
}
