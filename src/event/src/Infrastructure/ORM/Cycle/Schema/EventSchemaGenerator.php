<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Infrastructure\ORM\Cycle\Schema;

use Cycle\Schema\Definition\Entity;
use Cycle\Schema\Definition\Field;
use Cycle\Schema\Definition\Relation;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use FrameworkCompatibilityProject\Event\Domain\Model\Event;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslation;

class EventSchemaGenerator implements GeneratorInterface
{
    public function run(Registry $registry): Registry
    {
        $eventEntity = $this->createEventEntity();
        $eventTranslationEntity = $this->createEventTranslationEntity();

        $registry->register($eventEntity);
        $registry->register($eventTranslationEntity);

        $registry->linkTable($eventEntity, null, 'fcp_event');
        $registry->linkTable($eventTranslationEntity, null, 'fcp_event_translation');

        return $registry;
    }

    private function createEventEntity(): Entity
    {
        $entity = new Entity();
        $entity->setRole('event');
        $entity->setClass(Event::class);

        $entity->getFields()->set(
            'id',
            (new Field())
                ->setType('primary')
                ->setColumn('id')
                ->setPrimary(true)
        );

        $entity->getFields()->set(
            'defaultLocale',
            (new Field())
                ->setColumn('defaultLocale')
                ->setType('string(8)')
        );

        $entity->getRelations()->set(
            'translations',
            (new Relation())
                ->setTarget(EventTranslation::class)
                ->setType('hasMany')
                ->setInverse('event', 'belongsTo')
        );

        return $entity;
    }

    private function createEventTranslationEntity(): Entity
    {
        $entity = new Entity();
        $entity->setRole('event_translation');
        $entity->setClass(EventTranslation::class);

        $entity->getFields()->set(
            'id',
            (new Field())
                ->setType('primary')
                ->setColumn('id')
                ->setPrimary(true)
        );

        $entity->getFields()->set(
            'locale',
            (new Field())
                ->setType('string(8)')
                ->setColumn('locale')
        );

        $entity->getFields()->set(
            'title',
            (new Field())
                ->setType('string')
                ->setColumn('title')
                // TODO default ''
        );

        $entity->getRelations()->set(
            'event',
            (new Relation())
                ->setTarget(Event::class)
                ->setType('belongsTo')
                ->setInverse('translations', 'hasMany')
        );

        return $entity;
    }
}
