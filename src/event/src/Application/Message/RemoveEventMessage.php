<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Application\Message;

class RemoveEventMessage
{
    /**
     * @param array{
     *     id: int,
     * }|array<string, mixed> $identifier
     */
    public function __construct(private array $identifier)
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
}
