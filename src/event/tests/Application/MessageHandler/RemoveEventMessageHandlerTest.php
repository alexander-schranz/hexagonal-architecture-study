<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Application\MessageHandler;

use FrameworkCompatibilityProject\Event\Application\Message\RemoveEventMessage;
use FrameworkCompatibilityProject\Event\Application\MessageHandler\RemoveEventMessageHandler;
use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\InMemoryEventRepository;
use PHPUnit\Framework\TestCase;

class RemoveEventMessageHandlerTest extends TestCase
{
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var RemoveEventMessageHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        $this->eventRepository = new InMemoryEventRepository();
        $this->handler = new RemoveEventMessageHandler($this->eventRepository);
    }

    public function testInvokeNotExist(): void
    {
        $this->expectException(EventNotFoundException::class);

        $message = new RemoveEventMessage([
            'id' => \PHP_INT_MAX,
        ]);

        $this->handler->__invoke($message);
    }

    public function testInvoke(): void
    {
        $event = $this->eventRepository->create('sv');
        $this->eventRepository->createTranslation($event, 'sv')
            ->setTitle('Create Title');

        $this->eventRepository->add($event);
        $id = $event->getId();

        $this->assertNotNull($this->eventRepository->findOneBy(['id' => $id]));

        $message = new RemoveEventMessage([
            'id' => $id,
        ]);

        $this->handler->__invoke($message);

        $this->assertNull($this->eventRepository->findOneBy(['id' => $id]));
    }
}
