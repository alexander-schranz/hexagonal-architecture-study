<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Application\MessageHandler;

use FrameworkCompatibilityProject\Event\Application\Datamapper\EventDataMapperInterface;
use FrameworkCompatibilityProject\Event\Application\Message\CreateEventMessage;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;

final class CreateEventMessageHandler
{
    /**
     * @param iterable<EventDataMapperInterface> $eventDataMappers
     */
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private iterable $eventDataMappers
    ) {
    }

    public function __invoke(CreateEventMessage $message): EventInterface
    {
        $event = $this->eventRepository->create($message->getLocale());
        $eventTranslation = $this->eventRepository->createTranslation($event, $event->getDefaultLocale());

        foreach ($this->eventDataMappers as $eventDataMapper) {
            $eventDataMapper->map($event, $eventTranslation, $message->getData());
        }

        $this->eventRepository->add($event);

        return $event;
    }
}
