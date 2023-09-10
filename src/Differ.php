<?php

namespace Differ\Differ;

use Exception;
use Functional;

use function Differ\Formatters\formatRecords;
use function Differ\Parsers\parseFile;

/**
 * @throws Exception
 */
function genDiff(string $firstPath, string $secondPath, string $formatName = 'stylish'): string
{
    $first = (array)parseFile($firstPath);
    $second = (array)parseFile($secondPath);
    $diffTree = buildDiffTree($first, $second);
    return formatRecords($diffTree, $formatName);
}


function buildDiff(array $first, array $second): array
{
    $keys = array_unique(
        array_merge(
            array_keys($first),
            array_keys($second)
        )
    );

    $sortedKeys = Functional\sort($keys, fn($left, $right) => strcmp($left, $right));

    return array_map(function ($key) use ($first, $second) {

        $value1 = $first[$key] ?? null;
        $value2 = $second[$key] ?? null;

        if (!array_key_exists($key, $first)) {
            return [
                'key' => $key,
                'type' => 'added',
                'value' => $value2,
            ];
        }

        if (!array_key_exists($key, $second)) {
            return [
                'key' => $key,
                'type' => 'deleted',
                'value' => $value1,
            ];
        }

        if (is_array($value1) && is_array($value2)) {
            return [
                'key' => $key,
                'type' => 'nested',
                'children' => buildDiff($value1, $value2),
            ];
        }

        if ($value1 === $value2) {
            return [
                'key' => $key,
                'type' => 'unchanged',
                'value' => $value1,
            ];
        }

        return [
            'key' => $key,
            'type' => 'changed',
            'value1' => $value1,
            'value2' => $value2,
        ];
    }, $sortedKeys);
}

function buildDiffTree(array $first, array $second): array
{
    return [
        'type' => 'root',
        'children' => buildDiff($first, $second),
    ];
}
