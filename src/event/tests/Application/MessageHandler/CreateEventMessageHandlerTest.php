<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Application\MessageHandler;

use FrameworkCompatibilityProject\Event\Application\Datamapper\EventDataMapper;
use FrameworkCompatibilityProject\Event\Application\Message\CreateEventMessage;
use FrameworkCompatibilityProject\Event\Application\MessageHandler\CreateEventMessageHandler;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\InMemoryEventRepository;
use PHPUnit\Framework\TestCase;

class CreateEventMessageHandlerTest extends TestCase
{
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var CreateEventMessageHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        $this->eventRepository = new InMemoryEventRepository();
        $this->handler = new CreateEventMessageHandler(
            $this->eventRepository,
            [new EventDataMapper()]
        );
    }

    public function testInvoke(): void
    {
        $message = new CreateEventMessage([
            'locale' => 'sv',
            'title' => 'Create Title',
        ]);

        $event = $this->handler->__invoke($message);
        $id = $event->getId();

        $event = $this->eventRepository->getOneBy(['id' => $id]);
        $eventTranslation = $event->getTranslation('sv');
        $this->assertSame('sv', $eventTranslation->getLocale());
        $this->assertSame('Create Title', $eventTranslation->getTitle());
    }
}
