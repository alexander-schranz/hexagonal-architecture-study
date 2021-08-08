<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Application\MessageHandler;

use FrameworkCompatibilityProject\Event\Application\Datamapper\EventDataMapper;
use FrameworkCompatibilityProject\Event\Application\Message\ModifyEventMessage;
use FrameworkCompatibilityProject\Event\Application\MessageHandler\ModifyEventMessageHandler;
use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\InMemoryEventRepository;
use PHPUnit\Framework\TestCase;

class ModifyEventMessageHandlerTest extends TestCase
{
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var ModifyEventMessageHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        $this->eventRepository = new InMemoryEventRepository();
        $this->handler = new ModifyEventMessageHandler(
            $this->eventRepository,
            [new EventDataMapper()]
        );
    }

    public function testInvokeNotExist(): void
    {
        $this->expectException(EventNotFoundException::class);

        $message = new ModifyEventMessage([
            'id' => \PHP_INT_MAX,
        ], [
            'locale' => 'sv',
            'title' => 'Modify Title',
        ]);

        $this->handler->__invoke($message);
    }

    public function testInvokeExistLocale(): void
    {
        $event = $this->eventRepository->create('sv');
        $this->eventRepository->createTranslation($event, 'sv')
            ->setTitle('Create Title');

        $this->eventRepository->add($event);

        $id = $event->getId();

        $message = new ModifyEventMessage([
            'id' => $id,
        ], [
            'locale' => 'sv',
            'title' => 'Modify Title',
        ]);

        $event = $this->handler->__invoke($message);
        $id = $event->getId();

        $event = $this->eventRepository->getOneBy(['id' => $id]);
        $eventTranslation = $event->getTranslation('sv');
        $this->assertSame('sv', $eventTranslation->getLocale());
        $this->assertSame('Modify Title', $eventTranslation->getTitle());
    }

    public function testInvokeNoneExistLocale(): void
    {
        $event = $this->eventRepository->create('en');
        $this->eventRepository->createTranslation($event, 'en')
            ->setTitle('Create Title');

        $this->eventRepository->add($event);

        $id = $event->getId();

        $message = new ModifyEventMessage([
            'id' => $id,
        ], [
            'locale' => 'sv',
            'title' => 'Modify Title',
        ]);

        $event = $this->handler->__invoke($message);
        $id = $event->getId();

        $event = $this->eventRepository->getOneBy(['id' => $id]);
        $eventTranslation = $event->getTranslation('sv');
        $this->assertSame('sv', $eventTranslation->getLocale());
        $this->assertSame('Modify Title', $eventTranslation->getTitle());
    }
}
