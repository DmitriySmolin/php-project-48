<?php

namespace Differ\Formatters\Stylish;

use Exception;

/**
 * @throws Exception
 */
function render(array $node): string
{
    return nodeIterator($node, 0);
}

/**
 * @throws Exception
 */
function nodeIterator(array $tree, int $depth): string
{
    $indentation = buildIndent($depth);
    $formattedData = array_map(function ($node) use ($indentation, $depth): string {
        $nodeType = $node['type'];
        $nodeName = $node['name'];
        $formattedNode = "";
        switch ($nodeType) {
            case 'nested':
                $nestedNodes = nodeIterator($node['children'], $depth + 1);
                return "{$indentation}    {$nodeName}: {$nestedNodes}";
            case 'unchanged':
                $unchangedValue = stringify($node['value'], $depth + 1);
                return "{$indentation}    {$nodeName}: {$unchangedValue}";
            case 'changed':
                $value1 = stringify($node['valueBefore'], $depth + 1);
                $value2 = stringify($node['valueAfter'], $depth + 1);
                return "{$indentation}  - {$nodeName}: {$value1}\n{$indentation}  + {$nodeName}: {$value2}";
            case 'removed':
                $removedValue = stringify($node['value'], $depth + 1);
                return "{$indentation}  - {$nodeName}: {$removedValue}";
            case 'added':
                $addedValue = stringify($node['value'], $depth + 1);
                return "{$indentation}  + {$nodeName}: {$addedValue}";
        }
        return $formattedNode;
    }, $tree);
    $joinedData = implode("\n", $formattedData);
    return "{\n{$joinedData}\n{$indentation}}";
}

function stringify(mixed $data, int $depth = 0): string
{
    if (is_null($data)) {
        return 'null';
    }

    if (is_bool($data)) {
        return $data ? 'true' : 'false';
    }

    if (is_object($data)) {
        return formatArrToIndentedString(array_map(function ($key) use ($data, $depth): array {
            $value = $data->$key;
            return [
                'name' => $key,
                'value' => is_object($value) ? stringify($value, $depth + 1) : $value
            ];
        }, array_keys(get_object_vars($data))), $depth);
    }

    return (string)$data;
}

function formatArrToIndentedString(array $dataArray, int $depth): string
{
    $indentation = buildIndent($depth);
    $formattedString = array_map(function ($node) use ($depth, $indentation): string {
        if (is_array($node['value'])) {
            $formattedChildren = formatArrToIndentedString($node['value'], $depth + 1);
            return "{$indentation}    {$node['name']}: {$formattedChildren}";
        } else {
            return "{$indentation}    {$node['name']}: {$node['value']}";
        }
    }, $dataArray);
    $joinedString = implode("\n", $formattedString);
    return "{\n{$joinedString}\n{$indentation}}";
}

function buildIndent(int $depth): string
{
    return str_repeat('    ', $depth);
}
