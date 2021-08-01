<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Domain\Model;

use FrameworkCompatibilityProject\Event\Domain\Model\Event;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslation;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;
use FrameworkCompatibilityProject\Event\Tests\TestTraits\PrivatePropertyTrait;
use PHPUnit\Framework\TestCase;

class EventTransaltionTest extends TestCase
{
    use PrivatePropertyTrait;

    public function testGetLocale(): void
    {
        $event = $this->createEventTranslation(['locale' => 'de']);

        $this->assertSame('de', $event->getLocale());
    }

    public function testGetTitle(): void
    {
        $event = $this->createEventTranslation();

        $this->assertSame('', $event->getTitle());
    }

    public function testSetTitle(): void
    {
        $event = $this->createEventTranslation();

        $this->assertSame($event, $event->setTitle('Title'));
        $this->assertSame('Title', $event->getTitle());
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
     *     event?: EventInterface,
     *     locale?: string,
     * } $data
     */
    protected function createEventTranslation(array $data = []): EventTranslationInterface
    {
        return new EventTranslation(
            $data['event'] ?? static::createEvent(['defaultLocale' => $data['locale'] ?? 'en']),
            $data['locale'] ?? 'en',
        );
    }
}
