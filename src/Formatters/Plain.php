<?php

namespace Differ\Formatters\Plain;

use Exception;

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
function collectDiffLines(array $node, string $path = ""): array
{
    return array_reduce($node, function ($acc, $node) use ($path) {
        $type = $node['type'];
        $fullPath = "{$path}{$node['name']}";
        switch ($type) {
            case 'nested':
                $children = collectDiffLines($node['children'], "{$fullPath}.");
                return array_merge($acc, $children);
            case 'changed':
                $renderedValue1 = stringify($node['value1']);
                $renderedValue2 = stringify($node['value2']);
                return [...$acc, "Property '{$fullPath}' was updated. From {$renderedValue1} to {$renderedValue2}"];
            case 'removed':
                return [...$acc, "Property '{$fullPath}' was removed"];
            case 'added':
                $value = stringify($node['value']);
                return [...$acc, "Property '{$fullPath}' was added with value: {$value}"];
        }
        return $acc;
    }, []);
}


function stringify(mixed $value): string
{
    if (is_null($value)) {
        return 'null';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_object($value) || is_array($value)) {
        return "[complex value]";
    }
    return is_numeric($value) ? (string) $value : "'$value'";
}
