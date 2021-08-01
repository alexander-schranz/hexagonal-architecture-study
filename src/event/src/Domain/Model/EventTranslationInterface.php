<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Domain\Model;

interface EventTranslationInterface
{
    public function getLocale(): string;

    public function getTitle(): string;

    public function setTitle(string $title): static;
}
