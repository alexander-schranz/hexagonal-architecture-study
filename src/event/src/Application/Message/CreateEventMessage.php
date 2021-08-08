<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Application\Message;

class CreateEventMessage
{
    /**
     * @param array{
     *     locale: string,
     *     title: string,
     * }|array<string, mixed> $data
     */
    public function __construct(private array $data)
    {
    }

    public function getLocale(): string
    {
        return $this->data['locale'];
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    /**
     * @return array{
     *     locale: string,
     *     title: string,
     * }|array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
