<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Domain\Model;

use FrameworkCompatibilityProject\Event\Domain\Exception\EventTranslationNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Model\Event;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslation;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;
use FrameworkCompatibilityProject\Event\Tests\TestTraits\PrivatePropertyTrait;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    use PrivatePropertyTrait;

    public function testGetId(): void
    {
        $event = $this->createEvent(['defaultLocale' => 'de']);
        static::setPrivateProperty($event, 'id', 42);

        $this->assertSame(42, $event->getId());
    }

    public function testGetDefaultLocale(): void
    {
        $event = $this->createEvent(['defaultLocale' => 'de']);

        $this->assertSame('de', $event->getDefaultLocale());
    }

    public function testGetTranslations(): void
    {
        $event = $this->createEvent(['translations' => []]);
        $eventTranslationDe = $this->createEventTranslation($event, ['locale' => 'de']);
        $eventTranslationEn = $this->createEventTranslation($event, ['locale' => 'en']);

        $this->assertSame([$eventTranslationDe, $eventTranslationEn], (array) $event->getTranslations());
    }

    public function testRemoveTranslations(): void
    {
        $event = $this->createEvent(['translations' => []]);
        $this->createEventTranslation($event, ['locale' => 'de']);
        $this->createEventTranslation($event, ['locale' => 'en']);

        $this->assertCount(2, $event->getTranslations());
        $event->removeTranslation('en');
        $this->assertCount(1, $event->getTranslations());
    }

    public function testAddTranslations(): void
    {
        $event = $this->createEvent(['translations' => []]);
        $eventTranslationDe = $this->createEventTranslation($event, ['locale' => 'de']);
        $eventTranslationEn = $this->createEventTranslation($event, ['locale' => 'en']);

        $this->assertSame([$eventTranslationDe, $eventTranslationEn], (array) $event->getTranslations());
    }

    public function testGetTranslation(): void
    {
        $event = $this->createEvent(['translations' => []]);
        $eventTranslationDe = $this->createEventTranslation($event, ['locale' => 'de']);
        $this->createEventTranslation($event, ['locale' => 'en']);

        $this->assertSame($eventTranslationDe, $event->getTranslation('de'));
    }

    public function testGetTranslationNotExist(): void
    {
        $this->expectException(EventTranslationNotFoundException::class);

        $event = $this->createEvent(['translations' => []]);

        $event->getTranslation('de');
    }

    public function testFindTranslation(): void
    {
        $event = $this->createEvent(['translations' => []]);
        $eventTranslationDe = $this->createEventTranslation($event, ['locale' => 'de']);
        $this->createEventTranslation($event, ['locale' => 'en']);

        $this->assertSame($eventTranslationDe, $event->findTranslation('de'));
    }

    public function testFindTranslationNotExist(): void
    {
        $event = $this->createEvent(['translations' => []]);

        $this->assertNull($event->findTranslation('de'));
    }

    /**
     * @param array{
     *     defaultLocale?: string,
     *     translations?: iterable<EventTranslationInterface>,
     * } $data
     */
    protected function createEvent(array $data = []): EventInterface
    {
        return new Event(
            $data['defaultLocale'] ?? 'en',
            $data['translations'] ?? [],
        );
    }

    /**
     * @param array{
     *     locale?: string,
     * } $data
     */
    protected function createEventTranslation(EventInterface $event, array $data = []): EventTranslationInterface
    {
        return new EventTranslation(
            $event,
            $data['locale'] ?? 'en',
        );
    }
}
