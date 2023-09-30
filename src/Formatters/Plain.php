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

    $lines = [];

    switch ($type) {
        case 'root':
        case 'nested':
            $children = pick($node, 'children');
            foreach ($children as $child) {
                $lines = array_merge($lines, collectDiffLines($child, $path));
            }
            break;

        case 'changed':
            $renderedValue1 = stringify(pick($node, 'value1'));
            $renderedValue2 = stringify(pick($node, 'value2'));
            $lines = ["Property '$pathString' was updated. From $renderedValue1 to $renderedValue2"];
            break;

        case 'deleted':
            $lines = ["Property '$pathString' was removed"];
            break;

        case 'added':
            $value = pick($node, 'value');
            $renderedValue = stringify($value);
            $lines = ["Property '$pathString' was added with value: $renderedValue"];
            break;

        case 'unchanged':
            break;

        default:
            throw new \Exception("Unknown or nonexistent state");
    }

    return $lines;
}


function stringify(mixed $value): string
{
    if (is_array($value)) {
        return '[complex value]';
    }

    return var_export($value, true) === 'NULL' ? 'null' : var_export($value, true);
}
