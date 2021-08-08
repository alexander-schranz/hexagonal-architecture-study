<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Application\Message;

use FrameworkCompatibilityProject\Event\Application\Message\CreateEventMessage;
use PHPUnit\Framework\TestCase;

class CreateEventMessageTest extends TestCase
{
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
    protected function createMessage(array $data = []): CreateEventMessage
    {
        return new CreateEventMessage([
            'locale' => $data['locale'] ?? 'en',
            'title' => $data['title'] ?? 'Default Title',
        ]);
    }
}
