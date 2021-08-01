<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Domain\Exception;

class EventNotFoundException extends \Exception
{
    /**
     * @param mixed[] $filters
     */
    public function __construct(array $filters, int $code = 0, \Throwable $previous = null)
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
            'The even with %s not found.',
            implode(' and ', $filterText)
        );

        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
