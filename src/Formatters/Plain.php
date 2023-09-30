<?php

namespace Differ\Formatters\Plain;

use Exception;

use function Functional\pick;

/**
 * @throws Exception
 */
function renderPlain(array $tree): string
{
    $lines = collectDiffLines($tree);
    return implode("\n", $lines);
}

/**
 * @throws Exception
 */
function collectDiffLines(array $node, array $ancestry = []): array
{
    $type = pick($node, 'type');
    $key = pick($node, 'key');
    $path = array_filter(array_merge($ancestry, [$key]), fn($item) => $item !== null);
    $pathString = implode('.', $path);

    switch ($type) {
        case 'root':
        case 'nested':
            $children = pick($node, 'children');
            return array_reduce(array_map(function ($child) use ($path) {
                return collectDiffLines($child, $path);
            }, $children), 'array_merge', []);

        case 'changed':
            $renderedValue1 = stringify(pick($node, 'value1'));
            $renderedValue2 = stringify(pick($node, 'value2'));
            return ["Property '$pathString' was updated. From $renderedValue1 to $renderedValue2"];

        case 'deleted':
            return ["Property '$pathString' was removed"];

        case 'added':
            $value = pick($node, 'value');
            $renderedValue = stringify($value);
            return ["Property '$pathString' was added with value: $renderedValue"];

        case 'unchanged':
            return [];

        default:
            throw new \Exception("Unknown or nonexistent state");
    }
}


function stringify(mixed $value): string
{
    if (is_array($value)) {
        return '[complex value]';
    }

    return var_export($value, true) === 'NULL' ? 'null' : var_export($value, true);
}
