<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Domain\Repository;

use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\InMemoryEventRepository;
use PHPUnit\Framework\TestCase;

class InMemoryEventRepositoryTest extends TestCase
{
    use EventRepositoryTestTrait;

    protected function createEventRepository(): EventRepositoryInterface
    {
        return new InMemoryEventRepository();
    }
}
