<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Domain\Model;

class EventTranslation implements EventTranslationInterface
{
    /**
     * @var int
     */
    protected ?int $id = null;

    protected string $title = '';

    public function __construct(
        protected EventInterface $event,
        protected string $locale
    ) {
        $this->event->addTranslation($this);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
