<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Domain\Model;

use FrameworkCompatibilityProject\Event\Domain\Exception\EventTranslationNotFoundException;

interface EventInterface
{
    public function getId(): int;

    public function __toString(): string;

    public function getDefaultLocale(): string;

    /**
     * @return iterable<EventTranslationInterface>
     */
    public function getTranslations(): iterable;

    public function findTranslation(string $locale): ?EventTranslationInterface;

    /**
     * @throws EventTranslationNotFoundException
     */
    public function getTranslation(string $locale): EventTranslationInterface;

    public function addTranslation(EventTranslationInterface $translation): static;

    public function removeTranslation(string $locale): static;
}
