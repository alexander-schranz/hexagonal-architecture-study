<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Domain\Exception;

use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;

class EventTranslationNotFoundException extends \Exception
{
    /**
     * @param mixed[] $filters
     */
    public function __construct(EventInterface $event, array $filters, int $code = 0, \Throwable $previous = null)
    {
        $filterText = [];
        foreach ($filters as $key => $value) {
            if (\is_object($value)) {
                $value = get_debug_type($value);
            } else {
                $value = json_encode($value);
            }

            $filterText[] = sprintf('"%s" %s', $key, $value);
        }

        $message = sprintf(
            'The event translation with %s not found for event %s.',
            implode(' and ', $filterText),
            $event->__toString()
        );

        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
