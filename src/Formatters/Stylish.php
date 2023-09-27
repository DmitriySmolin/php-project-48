<?php

namespace Differ\Formatters\Stylish;

use Exception;

use function Functional\pick;

function renderStylish(array $node): string
{
    /**
     * @throws Exception
     */
    $iter = function ($node, $depth) use (&$iter) {

        $itemIndent = buildIndent($depth, 2);
        $bracketIndent = buildIndent($depth);

        $type = pick($node, 'type');

        switch ($type) {
            case 'root':
                $children = pick($node, 'children');
                $lines = array_map(
                    function ($node) use ($iter, $depth) {
                        return $iter($node, $depth);
                    },
                    $children
                );

                $result = ['{', ...$lines, '}'];
                return implode("\n", $result);

            case 'nested':
                $key = pick($node, 'key');
                $children = pick($node, 'children');

                $lines = array_map(
                    function ($node) use ($iter, $depth) {
                        return $iter($node, $depth + 1);
                    },
                    $children
                );

                $result = ["{$itemIndent}  {$key}: {", ...$lines, "{$bracketIndent}}"];
                return implode("\n", $result);

            case 'changed':
                $key = pick($node, 'key');

                $renderedValue1 = stringify(pick($node, 'value1'), $depth + 1);
                $renderedValue2 = stringify(pick($node, 'value2'), $depth + 1);

                $first = "{$itemIndent}- {$key}: {$renderedValue1}";
                $second = "{$itemIndent}+ {$key}: {$renderedValue2}";

                return implode("\n", [$first, $second]);

            case 'deleted':
                $key = pick($node, 'key');
                $value = pick($node, 'value');

                $renderedValue = stringify($value, $depth + 1);

                return "{$itemIndent}- {$key}: {$renderedValue}";

            case 'added':
                $key = pick($node, 'key');
                $value = pick($node, 'value');

                $renderedValue = stringify($value, $depth + 1);

                return "{$itemIndent}+ {$key}: {$renderedValue}";

            case 'unchanged':
                $key = pick($node, 'key');
                $value = pick($node, 'value');

                $renderedValue = stringify($value, $depth + 1);

                return "{$itemIndent}  {$key}: {$renderedValue}";

            default:
                throw new Exception("Unknown or not existed state");
        }
    };

    return $iter($node, 0);
}

function stringify(mixed $data, int $startDepth = 0, callable $toStringFn = null): string
{
    $toStringFn = $toStringFn ?? function (mixed $input): string {
        $exported = var_export($input, true);
        $exported = $exported === 'NULL' ? 'null' : $exported;
        return trim($exported, "'");
    };

    $iter = function ($data, $depth) use (&$iter, $toStringFn) {
        if (!is_array($data)) {
            return $toStringFn($data);
        }

        $itemIndent = buildIndent($depth);
        $bracketIndent = buildIndent($depth - 1);

        $lines = array_map(
            fn($key, $value) => "{$itemIndent}{$key}: {$iter($value, $depth + 1)}",
            array_keys($data),
            array_values($data)
        );

        $result = ['{', ...$lines, "{$bracketIndent}}"];
        return implode("\n", $result);
    };

    return $iter($data, $startDepth);
}

function buildIndent(int $depthOfNode, int $lengthOfTag = 0): string
{
    $depthOfElement = $depthOfNode + 1;
    return str_repeat(' ', 4 * $depthOfElement - $lengthOfTag);
}
