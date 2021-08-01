<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\TestTraits;

trait PrivatePropertyTrait
{
    /**
     * @return mixed
     */
    protected static function getPrivateProperty(object $object, string $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $propertyReflection = $reflection->getProperty($propertyName);
        $propertyReflection->setAccessible(true);

        return $propertyReflection->getValue($object);
    }

    /**
     * @param mixed $value
     */
    protected static function setPrivateProperty(object $object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $propertyReflection = $reflection->getProperty($propertyName);
        $propertyReflection->setAccessible(true);

        $propertyReflection->setValue($object, $value);
    }
}
