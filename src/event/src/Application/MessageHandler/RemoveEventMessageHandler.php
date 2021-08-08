<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Application\MessageHandler;

use FrameworkCompatibilityProject\Event\Application\Message\RemoveEventMessage;
use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;

final class RemoveEventMessageHandler
{
    public function __construct(private EventRepositoryInterface $eventRepository)
    {
    }

    /**
     * @throws EventNotFoundException
     */
    public function __invoke(RemoveEventMessage $message): void
    {
        $event = $this->eventRepository->getOneBy($message->getIdentifier());

        $this->eventRepository->remove($event);
    }
}
