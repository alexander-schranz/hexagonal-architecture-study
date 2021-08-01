<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Domain\Exception;

use FrameworkCompatibilityProject\Event\Domain\Exception\EventTranslationNotFoundException;
use FrameworkCompatibilityProject\Event\Domain\Model\Event;
use FrameworkCompatibilityProject\Event\Tests\TestTraits\PrivatePropertyTrait;
use PHPUnit\Framework\TestCase;

class EventTranslationNotFoundExceptionTest extends TestCase
{
    use PrivatePropertyTrait;

    public function testGetMessage(): void
    {
        $event = new Event('en');
        static::setPrivateProperty($event, 'id', 42);

        $exception = new EventTranslationNotFoundException($event, [
            'id' => 1,
            'object' => new \stdClass(),
            'array' => [
                'more' => 'test',
            ],
        ]);

        $this->assertSame(
            'The event translation with "id" 1 and "object" stdClass and "array" {"more":"test"} not found for event 42.',
            $exception->getMessage()
        );
    }

    public function testGetMessageWithoutId(): void
    {
        $event = new Event('en');

        $exception = new EventTranslationNotFoundException($event, [
            'id' => 1,
            'object' => new \stdClass(),
            'array' => [
                'more' => 'test',
            ],
        ]);

        $this->assertStringStartsWith(
            'The event translation with "id" 1 and "object" stdClass and "array" {"more":"test"} not found for ',
            $exception->getMessage()
        );
    }
}
