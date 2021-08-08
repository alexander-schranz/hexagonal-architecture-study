<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Application\Datamapper;

use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;

final class EventDataMapper implements EventDataMapperInterface
{
    /**
     * @param array{
     *     locale: string,
     *     title: string,
     * } $data
     */
    public function map(
        EventInterface $event,
        EventTranslationInterface $eventTranslation,
        array $data
    ): void {
        $eventTranslation->setTitle($data['title']);
    }
}
