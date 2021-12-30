<?php

namespace C01l\PhpDecoratorMemo;

use Attribute;
use C01l\PhpDecorator\Decorator;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Memo extends Decorator
{
    private array $cache = [];
    private array $keyDefinition;

    /**
     * @param array<string> $keyDefinition
     */
    public function __construct(array $keyDefinition = [])
    {
        $this->keyDefinition = array_map(function (string $def) {
            for ($i = 0; $i < strlen($def); $i++) {
                if (in_array($def[$i], ["-", "["], true)) {
                    break;
                }
            }
            $varName = substr($def, 0, $i);
            $code = substr($def, $i);
            return ["arg" => $varName, "func" => eval("return fn(\$_) => \$_" . $code . ";")];
        }, $keyDefinition);
    }

    private function extractValue(array $definition, array $args): string
    {
        return $definition["func"]($args[$definition["arg"]]);
    }

    private function cacheKey(array $args): string
    {
        return implode(
            "__",
            array_map(
                fn(array $def) => $this->extractValue($def, $args),
                $this->keyDefinition
            )
        );
    }

    public function wrap(callable $func): callable
    {
        return function (...$args) use ($func) {
            $key = $this->cacheKey($args);

            if (isset($this->cache[$key])) {
                return $this->cache[$key];
            }

            return $this->cache[$key] = $func(...$args);
        };
    }
}
