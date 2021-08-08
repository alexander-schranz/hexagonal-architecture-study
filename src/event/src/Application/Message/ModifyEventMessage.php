<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Application\Message;

class ModifyEventMessage
{
    /**
     * @param array{
     *     id: int,
     * }|array<string, mixed> $identifier
     * @param array{
     *     locale: string,
     *     title: string,
     * }|array<string, mixed> $data
     */
    public function __construct(private array $identifier, private array $data)
    {
    }

    /**
     * @return array{
     *     id: int,
     * }|array<string, mixed>
     */
    public function getIdentifier(): array
    {
        return $this->identifier;
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
