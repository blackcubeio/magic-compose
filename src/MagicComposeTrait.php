<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
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
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
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
        $cacheKey = static::class . '::' . $attributeClass;

        if (isset(self::$magicHandlers[$cacheKey]) === false) {
            $handlers = [];
            $reflection = new ReflectionClass(static::class);

            foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
                $attributes = $method->getAttributes($attributeClass);
                if (empty($attributes) === false) {
                    /** @var MagicAttribute $attr */
                    $attr = $attributes[0]->newInstance();
                    $handlers[] = [
                        'method' => $method->getName(),
                        'priority' => $attr->priority,
                    ];
                }
            }

            usort($handlers, fn($a, $b) => $b['priority'] <=> $a['priority']);

            self::$magicHandlers[$cacheKey] = $handlers;
        }

        return self::$magicHandlers[$cacheKey];
    }

    /**
     * Check if parent class has a specific magic method.
     * Cached per class.
     */
    private static function hasParentMagicMethod(string $methodName): bool
    {
        $cacheKey = static::class . '::' . $methodName;

        if (isset(self::$parentMagicMethods[$cacheKey]) === false) {
            $reflection = new ReflectionClass(static::class);
            $parent = $reflection->getParentClass();

            self::$parentMagicMethods[$cacheKey] = $parent !== false && $parent->hasMethod($methodName);
        }

        return self::$parentMagicMethods[$cacheKey];
    }

    public function __get(string $name): mixed
    {
        $value = null;
        $isHandled = false;
        foreach (self::discoverMagicHandlers(MagicGetter::class) as $handler) {
            if ($isHandled === false) {
                try {
                    $value = $this->{$handler['method']}($name);
                    $isHandled = true;
                } catch (MagicNotHandledException) {
                    $isHandled = false;
                }
            }
        }

        if ($isHandled === false) {
            if (self::hasParentMagicMethod('__get') === true) {
                $value = parent::__get($name);
            } else {
                trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_WARNING);
            }
        }

        return $value;
    }

    public function __set(string $name, mixed $value): void
    {
        $isHandled = false;
        foreach (self::discoverMagicHandlers(MagicSetter::class) as $handler) {
            if ($isHandled === false) {
                try {
                    $this->{$handler['method']}($name, $value);
                    $isHandled = true;
                } catch (MagicNotHandledException) {
                    $isHandled = false;
                }
            }
        }

        if ($isHandled === false) {
            if (self::hasParentMagicMethod('__set') === true) {
                parent::__set($name, $value);
            } else {
                trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_WARNING);
            }
        }
    }

    public function __isset(string $name): bool
    {
        $isSet = false;
        $isHandled = false;
        foreach (self::discoverMagicHandlers(MagicIsset::class) as $handler) {
            if ($isHandled === false) {
                try {
                    $isSet = $this->{$handler['method']}($name);
                    $isHandled = true;
                } catch (MagicNotHandledException) {
                    $isHandled = false;
                }
            }
        }

        foreach (self::discoverMagicHandlers(MagicGetter::class) as $handler) {
            if ($isHandled === false) {
                try {
                    $isSet = $this->{$handler['method']}($name) !== null;
                    $isHandled = true;
                } catch (MagicNotHandledException) {
                    $isHandled = false;
                }
            }
        }

        if ($isHandled === false) {
            if (self::hasParentMagicMethod('__isset') === true) {
                $isSet = parent::__isset($name);
            } else {
                trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_WARNING);
            }
        }

        return $isSet;
    }

    public function __unset(string $name): void
    {
        $isHandled = false;
        foreach (self::discoverMagicHandlers(MagicUnset::class) as $handler) {
            if ($isHandled === false) {
                try {
                    $this->{$handler['method']}($name);
                    $isHandled = true;
                } catch (MagicNotHandledException) {
                    $isHandled = false;
                }
            }
        }

        if ($isHandled === false) {
            if (self::hasParentMagicMethod('__unset') === true) {
                parent::__unset($name);
            } else {
                trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_WARNING);
                unset($this->$name);
            }
        }
    }

    public function __call(string $name, array $arguments): mixed
    {
        $result = null;
        $isHandled = false;
        foreach (self::discoverMagicHandlers(MagicCall::class) as $handler) {
            if ($isHandled === false) {
                try {
                    $result = $this->{$handler['method']}($name, $arguments);
                    $isHandled = true;
                } catch (MagicNotHandledException) {
                    $isHandled = false;
                }
            }
        }

        if ($isHandled === false) {
            if (self::hasParentMagicMethod('__call') === true) {
                $result = parent::__call($name, $arguments);
            } else {
                throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $name . '()');
            }
        }

        return $result;
    }
}
