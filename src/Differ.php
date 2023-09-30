<?php

namespace Differ\Differ;

use Exception;

use function Functional\sort;
use function Differ\Formatters\formatRecords;
use function Differ\Parsers\parseData;

/**
 * @throws Exception
 */
function genDiff(string $firstPath, string $secondPath, string $formatName = 'stylish'): string
{
    $firstArray = parseData(getFileData($firstPath));
    $secondArray = parseData(getFileData($secondPath));
    $diffTree = buildDiffTree($firstArray, $secondArray);
    return formatRecords($diffTree, $formatName);
}


function buildDiff(array $firstArray, array $secondArray): array
{
    $keys = array_unique(
        array_merge(
            array_keys($firstArray),
            array_keys($secondArray)
        )
    );

    $sortedKeys = sort($keys, fn(string $left, string $right) => strcmp($left, $right));

    return array_map(function (string $key) use ($firstArray, $secondArray) {

        $value1 = $firstArray[$key] ?? null;
        $value2 = $secondArray[$key] ?? null;

        if (!array_key_exists($key, $firstArray)) {
            return [
                'key' => $key,
                'type' => 'added',
                'value' => $value2,
            ];
        }

        if (!array_key_exists($key, $secondArray)) {
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

function buildDiffTree(array $firstArray, array $secondArray): array
{
    return [
        'type' => 'root',
        'children' => buildDiff($firstArray, $secondArray),
    ];
}

/**
 * @throws Exception
 */
function getFileData(string $filePath): array
{
    if (!file_exists($filePath)) {
        throw new Exception("File not found: {$filePath}");
    }

    $format = pathinfo($filePath, PATHINFO_EXTENSION);

    $data = file_get_contents($filePath);

    return [$format, $data];
}
