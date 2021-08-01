<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Domain\Repository;

use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;

interface EventRepositoryInterface
{
    public function create(string $defaultLocale): EventInterface;

    public function createTranslation(EventInterface $event, string $locale): EventTranslationInterface;

    public function add(EventInterface $event): void;

    public function remove(EventInterface $event): void;

    /**
     * @param array{
     *     id?: int,
     * } $filters
     *
     * @throws EventNotFoundException
     */
    public function getOneBy(array $filters): EventInterface;

    public function findOneBy(array $filters): ?EventInterface;

    /**
     * @param array{
     *     ids?: int[],
     *     locale?: string,
     *     page?: int,
     *     limit?: int,
     * } $filters
     * @param array<'id'|'key'|'title', 'asc'|'desc'> $sortBys
     *
     * @return iterable<array{
     *     id: int,
     *     defaultLocale: string,
     *     title: string,
     * }>
     */
    public function findFlat(array $filters = [], array $sortBys = []): iterable;

    /**
     * @param array{
     *     ids?: int[],
     *     locale?: string,
     *     page?: int,
     *     limit?: int,
     * } $filters
     * @param array<'id'|'key'|'title', 'asc'|'desc'> $sortBys
     *
     * @return iterable<EventInterface>
     */
    public function findBy(array $filters = [], array $sortBys = []): iterable;

    /**
     * @param array{
     *     ids?: int[],
     *     locale?: string,
     * } $filters
     */
    public function countBy(array $filters = []): int;
}
