<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Application\Datamapper;

use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;

/**
 * All including this interface should be injected into the
 * CreateEventMessageHandler and ModifyEventMessageHandler
 * so additional data can be mapped to the Event Model.
 */
interface EventDataMapperInterface
{
    /**
     * @param mixed[] $data
     */
    public function map(
        EventInterface $event,
        EventTranslationInterface $eventTranslation,
        array $data
    ): void;
}
