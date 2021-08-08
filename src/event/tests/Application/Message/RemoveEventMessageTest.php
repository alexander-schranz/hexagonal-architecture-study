<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Application\Message;

use FrameworkCompatibilityProject\Event\Application\Message\RemoveEventMessage;
use PHPUnit\Framework\TestCase;

class RemoveEventMessageTest extends TestCase
{
    public function testGetIdentifier(): void
    {
        $message = $this->createMessage(['id' => 2]);
        $this->assertSame(['id' => 2], $message->getIdentifier());
    }

    /**
     * @param mixed[] $data
     */
    protected function createMessage(array $data = []): RemoveEventMessage
    {
        return new RemoveEventMessage([
            'id' => $data['id'] ?? 1,
        ]);
    }
}
