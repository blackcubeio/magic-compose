<?php

declare(strict_types=1);

/**
 * MagicComposeMethodsBaseTrait.php
 *
 * PHP Version 8.4
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */

namespace Blackcube\MagicCompose;

use Blackcube\MagicCompose\Attributes\MagicExtend;
use ReflectionClass;
use ReflectionMethod;

/**
 * Trait providing MagicExtend discovery and chain execution.
 * Reusable base for framework-specific compose traits.
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
trait MagicComposeMethodsBaseTrait
{
    /**
     * @var array<string, array<array{method: string, priority: int}>>
     */
    private static array $magicExtendHandlers = [];

    /**
     * @var array<callable>
     */
    private array $nextStack = [];

    /**
     * Call the next handler in the chain.
     * Must be called from within a MagicExtend handler.
     */
    protected function next(mixed ...$args): mixed
    {
        $current = end($this->nextStack);
        if ($current === false) {
            throw new \LogicException('next() called outside of MagicExtend chain');
        }
        return $current(...$args);
    }

    /**
     * Discover MagicExtend handlers for a specific method name.
     * Cached per class.
     *
     * @return array<array{method: string, priority: int}>
     */
    private static function discoverMagicExtendHandlers(string $methodName): array
    {
        $cacheKey = static::class.'::extend::'.$methodName;

        if (isset(self::$magicExtendHandlers[$cacheKey]) === true) {
            return self::$magicExtendHandlers[$cacheKey];
        }

        $handlers = [];
        $reflection = new ReflectionClass(static::class);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
            $attributes = $method->getAttributes(MagicExtend::class);
            if (empty($attributes) === true) {
                continue;
            }

            /** @var MagicExtend $attr */
            $attr = $attributes[0]->newInstance();

            if ($attr->method !== $methodName) {
                continue;
            }

            $handlers[] = [
                'method' => $method->getName(),
                'priority' => $attr->priority,
            ];
        }

        usort($handlers, fn($a, $b) =>
            $b['priority'] <=> $a['priority'] ?: $a['method'] <=> $b['method']
        );

        self::$magicExtendHandlers[$cacheKey] = $handlers;
        return $handlers;
    }

    /**
     * Execute the extend chain for a method.
     *
     * @param string $methodName Method being extended
     * @param callable $parent Final callable (parent::method)
     * @param array $args Arguments to pass
     */
    private function executeExtendChain(string $methodName, callable $parent, array $args): mixed
    {
        $handlers = self::discoverMagicExtendHandlers($methodName);

        $next = fn(...$a) => $parent(...$a);

        foreach (array_reverse($handlers) as $handler) {
            $currentNext = $next;
            $next = function (...$a) use ($handler, $currentNext) {
                $this->nextStack[] = $currentNext;
                try {
                    return $this->{$handler['method']}(...$a);
                } finally {
                    array_pop($this->nextStack);
                }
            };
        }

        return $next(...$args);
    }
}
