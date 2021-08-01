<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Domain\Exception;

use FrameworkCompatibilityProject\Event\Domain\Exception\EventNotFoundException;
use PHPUnit\Framework\TestCase;

class EventNotFoundExceptionTest extends TestCase
{
    public function testGetMessage(): void
    {
        $exception = new EventNotFoundException([
            'id' => 1,
            'object' => new \stdClass(),
            'array' => [
                'more' => 'test',
            ],
        ]);

        $this->assertSame(
            'The even with "id" 1 and "object" stdClass and "array" {"more":"test"} not found.',
            $exception->getMessage()
        );
    }
}
