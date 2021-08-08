<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Application\Message;

use FrameworkCompatibilityProject\Event\Application\Message\ModifyEventMessage;
use PHPUnit\Framework\TestCase;

class ModifyEventMessageTest extends TestCase
{
    public function testGetIdentifier(): void
    {
        $message = $this->createMessage(['id' => 2]);
        $this->assertSame(['id' => 2], $message->getIdentifier());
    }

    public function testGetLocale(): void
    {
        $message = $this->createMessage(['locale' => 'sv']);
        $this->assertSame('sv', $message->getLocale());
    }

    public function testGetData(): void
    {
        $message = $this->createMessage([
            'locale' => 'sv',
            'title' => 'My Title',
        ]);
        $this->assertSame([
            'locale' => 'sv',
            'title' => 'My Title',
        ], $message->getData());
    }

    /**
     * @param mixed[] $data
     */
    protected function createMessage(array $data = []): ModifyEventMessage
    {
        return new ModifyEventMessage([
            'id' => $data['id'] ?? 1,
        ], [
            'locale' => $data['locale'] ?? 'en',
            'title' => $data['title'] ?? 'Default Title',
        ]);
    }
}
