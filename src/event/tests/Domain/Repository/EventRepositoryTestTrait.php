<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Domain\Repository;

use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;

trait EventRepositoryTestTrait
{
    public function testCreate(): void
    {
        $eventRepository = $this->createEventRepository();

        $this->assertInstanceOf(EventInterface::class, $eventRepository->create('en'));
    }

    public function testCreateTranslation(): void
    {
        $eventRepository = $this->createEventRepository();

        $event = $eventRepository->create('en');
        $this->assertInstanceOf(EventTranslationInterface::class, $eventRepository->createTranslation($event, 'en'));
    }

    public function testAdd(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventB = $eventRepository->create('en');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();

        $events = [...$eventRepository->findBy(['ids' => [$eventAId, $eventBId]])];

        $this->assertCount(
            2,
            $events
        );
    }

    public function testRemove(): void
    {
        $this->purge();

        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventB = $eventRepository->create('en');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();

        $eventRepository->remove($eventA);
        $this->flush();

        $events = [...$eventRepository->findBy(['ids' => [$eventAId, $eventBId]])];

        $this->assertCount(
            1,
            $events
        );

        $this->assertSame($eventBId, $events[0]->getId());
    }

    public function testCountBy(): void
    {
        $this->purge();

        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventB = $eventRepository->create('en');
        $eventC = $eventRepository->create('en');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);
        $eventRepository->add($eventC);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();

        $this->assertSame(2, $eventRepository->countBy(['ids' => [$eventAId, $eventBId]]));
    }

    public function testGetGetOneByNotExist(): void
    {
        $this->expectException(EventNotFoundException::class);

        $eventRepository = $this->createEventRepository();

        $eventRepository->getOneBy(['id' => \PHP_INT_MAX]);
    }

    public function testGetOneById(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->add($eventA);
        $this->flush();
        $eventAId = $eventA->getId();

        $this->assertSame(
            $eventAId,
            $eventRepository->getOneBy(['id' => $eventAId])->getId()
        );
    }

    public function testGetFindOneByNotExist(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->add($eventA);

        $this->assertNull($eventRepository->findOneBy(['id' => \PHP_INT_MAX]));
    }

    public function testGetFindOneId(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->add($eventA);

        $this->assertNull($eventRepository->findOneBy(['id' => \PHP_INT_MAX]));
    }

    public function testFindGetOneBy(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->add($eventA);
        $this->flush();
        $eventAId = $eventA->getId();

        $this->assertSame(
            $eventAId,
            $eventRepository->findOneBy(['id' => $eventAId])->getId()
        );
    }

    public function testFlatByIds(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->createTranslation($eventA, 'en');
        $eventB = $eventRepository->create('en');
        $eventRepository->createTranslation($eventB, 'en');
        $eventC = $eventRepository->create('de');
        $eventRepository->createTranslation($eventC, 'de');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);
        $eventRepository->add($eventC);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();

        $eventList = [...$eventRepository->findFlat(['ids' => [$eventAId, $eventBId, \PHP_INT_MAX]])];

        $this->assertCount(2, $eventList);

        $this->assertSame([
            [
                'id' => $eventAId,
                'defaultLocale' => 'en',
                'title' => '',
            ],
            [
                'id' => $eventBId,
                'defaultLocale' => 'en',
                'title' => '',
            ],
        ], $eventList);
    }

    public function testFlatByIdsAndLocale(): void
    {
        $this->purge();

        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->createTranslation($eventA, 'en');
        $eventRepository->createTranslation($eventA, 'it');
        $eventB = $eventRepository->create('en');
        $eventRepository->createTranslation($eventB, 'en');
        $eventRepository->createTranslation($eventB, 'sv');
        $eventC = $eventRepository->create('de');
        $eventRepository->createTranslation($eventC, 'de');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);
        $eventRepository->add($eventC);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();
        $eventCId = $eventC->getId();

        $eventList = [...$eventRepository->findFlat(['ids' => [$eventAId, $eventBId, $eventCId], 'locale' => 'en'])];

        $this->assertCount(2, $eventList);

        $this->assertSame([
            [
                'id' => $eventAId,
                'defaultLocale' => 'en',
                'title' => '',
            ],
            [
                'id' => $eventBId,
                'defaultLocale' => 'en',
                'title' => '',
            ],
        ], $eventList);
    }

    public function testFlatByIdsAndSortByTitle(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->createTranslation($eventA, 'en')
            ->setTitle('1 Title');
        $eventB = $eventRepository->create('en');
        $eventRepository->createTranslation($eventB, 'en')
            ->setTitle('3 Title');
        $eventC = $eventRepository->create('en');
        $eventRepository->createTranslation($eventC, 'en')
            ->setTitle('2 Title');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);
        $eventRepository->add($eventC);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();
        $eventCId = $eventC->getId();

        $eventList = [
            ...$eventRepository->findFlat(
                ['ids' => [$eventAId, $eventBId, $eventCId], 'locale' => 'en'],
                ['title' => 'asc']
            ),
        ];

        $this->assertCount(3, $eventList);

        $this->assertSame([
            [
                'id' => $eventAId,
                'defaultLocale' => 'en',
                'title' => '1 Title',
            ],
            [
                'id' => $eventCId,
                'defaultLocale' => 'en',
                'title' => '2 Title',
            ],
            [
                'id' => $eventBId,
                'defaultLocale' => 'en',
                'title' => '3 Title',
            ],
        ], $eventList);
    }

    public function testFlatByIdsAndSortByDefaultTitle(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->createTranslation($eventA, 'en')
            ->setTitle('1 Title');
        $eventB = $eventRepository->create('en');
        $eventRepository->createTranslation($eventB, 'en')
            ->setTitle('3 Title');
        $eventC = $eventRepository->create('de');
        $eventRepository->createTranslation($eventC, 'de')
            ->setTitle('2 Title');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);
        $eventRepository->add($eventC);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();
        $eventCId = $eventC->getId();

        $eventList = [
            ...$eventRepository->findFlat(
                ['ids' => [$eventAId, $eventBId, $eventCId]],
                ['title' => 'asc']
            ),
        ];

        $this->assertCount(3, $eventList);

        $this->assertSame([
            [
                'id' => $eventAId,
                'defaultLocale' => 'en',
                'title' => '1 Title',
            ],
            [
                'id' => $eventCId,
                'defaultLocale' => 'de',
                'title' => '2 Title',
            ],
            [
                'id' => $eventBId,
                'defaultLocale' => 'en',
                'title' => '3 Title',
            ],
        ], $eventList);
    }

    public function testFindByIds(): void
    {
        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventB = $eventRepository->create('en');
        $eventC = $eventRepository->create('en');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);
        $eventRepository->add($eventC);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();

        $eventIds = array_map(
            function (EventInterface $event) {
                return $event->getId();
            },
            [...$eventRepository->findBy(['ids' => [$eventAId, $eventBId, \PHP_INT_MAX]])]
        );

        $this->assertCount(2, $eventIds);
        $this->assertContains($eventAId, $eventIds);
        $this->assertContains($eventBId, $eventIds);
    }

    public function testFindByLocale(): void
    {
        $this->purge();

        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventRepository->createTranslation($eventA, 'en');
        $eventB = $eventRepository->create('en');
        $eventRepository->createTranslation($eventB, 'en');
        $eventC = $eventRepository->create('de');
        $eventRepository->createTranslation($eventC, 'de');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);
        $eventRepository->add($eventC);

        $this->flush();

        $this->assertCount(2, $eventRepository->findBy(['locale' => 'en']));
        $this->assertCount(1, $eventRepository->findBy(['locale' => 'de']));
    }

    public function testFindByPageAndLimit(): void
    {
        $this->purge();

        $eventRepository = $this->createEventRepository();

        $eventA = $eventRepository->create('en');
        $eventB = $eventRepository->create('en');
        $eventC = $eventRepository->create('en');

        $eventRepository->add($eventA);
        $eventRepository->add($eventB);
        $eventRepository->add($eventC);

        $this->flush();
        $eventAId = $eventA->getId();
        $eventBId = $eventB->getId();
        $eventCId = $eventC->getId();

        $eventIds = array_map(
            function (EventInterface $event) {
                return $event->getId();
            },
            [...$eventRepository->findBy(['page' => 1, 'limit' => 2], ['id' => 'asc'])]
        );

        $this->assertCount(2, $eventIds);
        $this->assertContains($eventAId, $eventIds);
        $this->assertContains($eventBId, $eventIds);

        $eventIds = array_map(
            function (EventInterface $event) {
                return $event->getId();
            },
            [...$eventRepository->findBy(['page' => 2, 'limit' => 2])]
        );

        $this->assertCount(1, $eventIds);
        $this->assertContains($eventCId, $eventIds);
    }

    abstract protected function createEventRepository(): EventRepositoryInterface;

    /**
     * Called when all added models should be flushed.
     */
    protected function flush(): void
    {
    }

    /**
     * Called for tests which should not contain additional events.
     */
    protected function purge()
    {
    }
}
