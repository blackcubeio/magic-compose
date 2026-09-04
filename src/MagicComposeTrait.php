<?php

declare(strict_types=1);

/**
 * MagicComposeTrait.php
 *
 * PHP Version 8.4
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */

namespace Blackcube\MagicCompose;

use Blackcube\MagicCompose\Attributes\MagicAttribute;
use Blackcube\MagicCompose\Attributes\MagicCall;
use Blackcube\MagicCompose\Attributes\MagicGetter;
use Blackcube\MagicCompose\Attributes\MagicIsset;
use Blackcube\MagicCompose\Attributes\MagicSetter;
use Blackcube\MagicCompose\Attributes\MagicUnset;
use Blackcube\MagicCompose\Exceptions\MagicNotHandledException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Trait that auto-discovers and dispatches magic methods based on attributes.
 *
 * Usage:
 * ```php
 * class MyClass {
 *     use MagicComposeTrait, SomeTrait, OtherTrait;
 * }
 * ```
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
trait MagicComposeTrait
{
    /**
     * @var array<string, array<array{method: string, priority: int}>>
     */
    private static array $magicHandlers = [];

    /**
     * @var array<string, bool>
     */
    private static array $parentMagicMethods = [];

    /**
     * Discover handlers for a specific attribute type.
     * Cached per class.
     *
     * @param class-string<MagicAttribute> $attributeClass
     * @return array<array{method: string, priority: int}>
     */
    private static function discoverMagicHandlers(string $attributeClass): array
    {
        $cacheKey = static::class.'::'.$attributeClass;

        if (isset(self::$magicHandlers[$cacheKey]) === true) {
            return self::$magicHandlers[$cacheKey];
        }

        $handlers = [];
        $reflection = new ReflectionClass(static::class);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
            $attributes = $method->getAttributes($attributeClass);
            if (empty($attributes) === true) {
                continue;
            }

            /** @var MagicAttribute $attr */
            $attr = $attributes[0]->newInstance();
            $handlers[] = [
                'method' => $method->getName(),
                'priority' => $attr->priority,
            ];
        }

        usort($handlers, fn($a, $b) => $b['priority'] <=> $a['priority']);

        self::$magicHandlers[$cacheKey] = $handlers;
        return $handlers;
    }

    /**
     * Check if parent class has a specific magic method.
     * Cached per class.
     */
    private static function hasParentMagicMethod(string $methodName): bool
    {
        $cacheKey = static::class.'::'.$methodName;

        if (isset(self::$parentMagicMethods[$cacheKey]) === true) {
            return self::$parentMagicMethods[$cacheKey];
        }

        $reflection = new ReflectionClass(static::class);
        $parent = $reflection->getParentClass();

        $hasMethod = $parent !== false && $parent->hasMethod($methodName);

        self::$parentMagicMethods[$cacheKey] = $hasMethod;
        return $hasMethod;
    }

    public function __get(string $name): mixed
    {
        foreach (self::discoverMagicHandlers(MagicGetter::class) as $handler) {
            try {
                return $this->{$handler['method']}($name);
            } catch (MagicNotHandledException) {
                continue;
            }
        }

        if (self::hasParentMagicMethod('__get')) {
            return parent::__get($name);
        }

        trigger_error('Undefined property: '.static::class.'::$'.$name, E_USER_WARNING);
        return null;
    }

    public function __set(string $name, mixed $value): void
    {
        foreach (self::discoverMagicHandlers(MagicSetter::class) as $handler) {
            try {
                $this->{$handler['method']}($name, $value);
                return;
            } catch (MagicNotHandledException) {
                continue;
            }
        }

        if (self::hasParentMagicMethod('__set')) {
            parent::__set($name, $value);
            return;
        }

        trigger_error('Undefined property: '.static::class.'::$'.$name, E_USER_WARNING);
        $this->$name = $value;
    }

    public function __isset(string $name): bool
    {
        foreach (self::discoverMagicHandlers(MagicIsset::class) as $handler) {
            try {
                return $this->{$handler['method']}($name);
            } catch (MagicNotHandledException) {
                continue;
            }
        }

        foreach (self::discoverMagicHandlers(MagicGetter::class) as $handler) {
            try {
                return $this->{$handler['method']}($name) !== null;
            } catch (MagicNotHandledException) {
                continue;
            }
        }

        if (self::hasParentMagicMethod('__isset')) {
            return parent::__isset($name);
        }

        trigger_error('Undefined property: '.static::class.'::$'.$name, E_USER_WARNING);
        return false;
    }

    public function __unset(string $name): void
    {
        foreach (self::discoverMagicHandlers(MagicUnset::class) as $handler) {
            try {
                $this->{$handler['method']}($name);
                return;
            } catch (MagicNotHandledException) {
                continue;
            }
        }

        if (self::hasParentMagicMethod('__unset')) {
            parent::__unset($name);
            return;
        }

        trigger_error('Undefined property: '.static::class.'::$'.$name, E_USER_WARNING);
        unset($this->$name);
    }

    public function __call(string $name, array $arguments): mixed
    {
        foreach (self::discoverMagicHandlers(MagicCall::class) as $handler) {
            try {
                return $this->{$handler['method']}($name, $arguments);
            } catch (MagicNotHandledException) {
                continue;
            }
        }

        if (self::hasParentMagicMethod('__call')) {
            return parent::__call($name, $arguments);
        }

        throw new \BadMethodCallException('Call to undefined method '.static::class.'::'.$name.'()');
    }
}
