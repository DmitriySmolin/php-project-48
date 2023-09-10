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
function collectDiffLines(array $node, array $lines = [], array $ancestry = []): array
{
    $type = pick($node, 'type');
    $key = pick($node, 'key');
    $path = buildPathToCurrentNode($ancestry, $key);
    $pathString = implode('.', $path);

    switch ($type) {
        case 'root':
        case 'nested':
            $children = pick($node, 'children');
            return array_reduce(
                $children,
                fn($lines, $child) => collectDiffLines(
                    $child,
                    $lines,
                    $path,
                ),
                $lines
            );

        case 'changed':
            $renderedValue1 = stringifyValue(pick($node, 'value1'));
            $renderedValue2 = stringifyValue(pick($node, 'value2'));
            return array_merge(
                $lines,
                ["Property '{$pathString}' was updated. From {$renderedValue1} to {$renderedValue2}"]
            );

        case 'deleted':
            return array_merge(
                $lines,
                ["Property '{$pathString}' was removed"]
            );

        case 'added':
            $value = pick($node, 'value');
            $renderedValue = stringifyValue($value);
            return array_merge(
                $lines,
                ["Property '{$pathString}' was added with value: {$renderedValue}"]
            );

        case 'unchanged':
            return $lines;

        default:
            throw new \Exception("Unknown or nonexistent state");
    }
}

function buildPathToCurrentNode(array $path, ?string $key): array
{
    return array_filter(
        array_merge($path, [$key]),
        fn($item) => $item !== null
    );
}

function stringifyValue(mixed $value): string
{
    return is_array($value) ? '[complex value]' : convertToString($value);
}

function convertToString(mixed $input): string
{
    return var_export($input, true) === 'NULL' ? 'null' : var_export($input, true);
}
