<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Domain\Model;

use FrameworkCompatibilityProject\Event\Domain\Exception\EventTranslationNotFoundException;

class Event implements EventInterface
{
    /**
     * @var int
     */
    protected ?int $id = null;

    /**
     * @param iterable<EventTranslationInterface>
     */
    public function __construct(
        protected string $defaultLocale,
        protected iterable $translations = []
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return isset($this->id) ? (string) $this->id : spl_object_hash($this);
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    public function getTranslations(): iterable
    {
        return $this->translations;
    }

    public function findTranslation(string $locale): ?EventTranslationInterface
    {
        /** @var EventTranslationInterface $translation */
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    public function getTranslation(string $locale): EventTranslationInterface
    {
        /** @var EventTranslationInterface $translation */
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        throw new EventTranslationNotFoundException($this, ['locale' => $locale]);
    }

    public function addTranslation(EventTranslationInterface $translation): static
    {
        $this->translations[] = $translation;

        return $this;
    }

    public function removeTranslation(string $locale): static
    {
        /** @var EventTranslationInterface $translation */
        foreach ($this->translations as $key => $translation) {
            if ($translation->getLocale() === $locale) {
                unset($this->translations[$key]);
            }
        }

        return $this;
    }
}
