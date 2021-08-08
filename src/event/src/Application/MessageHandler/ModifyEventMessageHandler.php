<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Application\MessageHandler;

use FrameworkCompatibilityProject\Event\Application\Datamapper\EventDataMapperInterface;
use FrameworkCompatibilityProject\Event\Application\Message\ModifyEventMessage;
use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;

final class ModifyEventMessageHandler
{
    /**
     * @param iterable<EventDataMapperInterface> $eventDataMappers
     */
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private iterable $eventDataMappers
    ) {
    }

    /**
     * @throws EventNotFoundException
     */
    public function __invoke(ModifyEventMessage $message): EventInterface
    {
        $event = $this->eventRepository->getOneBy($message->getIdentifier());

        $eventTranslation = $event->findTranslation($message->getLocale());

        if (!$eventTranslation) {
            $eventTranslation = $this->eventRepository->createTranslation($event, $message->getLocale());
        }

        foreach ($this->eventDataMappers as $eventDataMapper) {
            $eventDataMapper->map($event, $eventTranslation, $message->getData());
        }

        return $event;
    }
}
