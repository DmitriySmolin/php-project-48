<?php

namespace Differ\Formatters\Stylish;

use Exception;

use function Functional\pick;

/**
 * @throws Exception
 */
function renderStylish(array $node): string
{
    return nodeIterator($node, 0);
}

/**
 * @throws Exception
 */
function nodeIterator(array $node, int $depth): string
{
    $itemIndent = buildIndent($depth, 2);
    $bracketIndent = buildIndent($depth);

    $type = pick($node, 'type');

    switch ($type) {
        case 'root':
            $children = pick($node, 'children');
            $lines = array_map(
                function (array $node) use ($depth) {
                    return nodeIterator($node, $depth);
                },
                $children
            );

            $result = ['{', ...$lines, '}'];
            return implode("\n", $result);

        case 'nested':
            $key = pick($node, 'key');
            $children = pick($node, 'children');

            $lines = array_map(
                function (array $node) use ($depth) {
                    return nodeIterator($node, $depth + 1);
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
}

function stringify(mixed $data, int $depth = 0): string
{
    if (!is_array($data)) {
        return trim($data === null ? 'null' : var_export($data, true), "'");
    }

    $itemIndent = buildIndent($depth);
    $bracketIndent = buildIndent($depth - 1);

    $lines = array_map(function ($key, $value) use ($depth, $itemIndent) {
        return "{$itemIndent}" . stringify($key) . ": " . stringify($value, $depth + 1);
    }, array_keys($data), $data);

    return implode("\n", ['{', ...$lines, "{$bracketIndent}}"]);
}

function buildIndent(int $depthOfNode, int $lengthOfTag = 0): string
{
    $depthOfElement = $depthOfNode + 1;
    return str_repeat(' ', 4 * $depthOfElement - $lengthOfTag);
}
