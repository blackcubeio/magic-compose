<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose;

use Blackcube\MagicCompose\Attributes\MagicExtend;
use ReflectionClass;
use ReflectionMethod;

/**
 * Trait providing MagicExtend discovery and chain execution.
 * Reusable base for framework-specific compose traits.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
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
        $cacheKey = static::class . '::extend::' . $methodName;

        if (isset(self::$magicExtendHandlers[$cacheKey]) === false) {
            $handlers = [];
            $reflection = new ReflectionClass(static::class);

            foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
                $attributes = $method->getAttributes(MagicExtend::class);
                if (empty($attributes) === false) {
                    /** @var MagicExtend $attr */
                    $attr = $attributes[0]->newInstance();

                    if ($attr->method === $methodName) {
                        $handlers[] = [
                            'method' => $method->getName(),
                            'priority' => $attr->priority,
                        ];
                    }
                }
            }

            usort($handlers, fn($a, $b) =>
                $b['priority'] <=> $a['priority'] ?: $a['method'] <=> $b['method']
            );

            self::$magicExtendHandlers[$cacheKey] = $handlers;
        }

        return self::$magicExtendHandlers[$cacheKey];
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
            $next = fn(...$a) => $this->runExtendHandler($handler, $currentNext, $a);
        }

        return $next(...$args);
    }

    /**
     * Runs one MagicExtend handler with its next() callable pushed on the stack.
     *
     * @param array{method: string, priority: int} $handler
     */
    private function runExtendHandler(array $handler, callable $currentNext, array $args): mixed
    {
        $this->nextStack[] = $currentNext;
        try {
            $result = $this->{$handler['method']}(...$args);
        } finally {
            array_pop($this->nextStack);
        }
        return $result;
    }
}
