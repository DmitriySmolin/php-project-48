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
    $firstObj = getFileData($firstPath);
    $secondObj = getFileData($secondPath);
    $diffTree = buildDiff($firstObj, $secondObj);
    return formatRecords($diffTree, $formatName);
}

function buildDiff(object $firstObj, object $secondObj): array
{
    $getKeys = fn($obj) => array_keys(get_object_vars($obj));

    $keys = array_unique(array_merge($getKeys($firstObj), $getKeys($secondObj)));

    $sortedKeys = array_values(sort($keys, fn($left, $right) => strcmp($left, $right)));

    return array_map(function (mixed $key) use ($firstObj, $secondObj) {

        if (!property_exists($secondObj, $key)) {
            return [
                'name' => $key,
                'type' => 'removed',
                'value' => $firstObj->$key
            ];
        }
        if (!property_exists($firstObj, $key)) {
            return [
                'name' => $key,
                'type' => 'added',
                'value' => $secondObj->$key
            ];
        }
        if (is_object($firstObj->$key) && is_object($secondObj->$key)) {
            return [
                'name' => $key,
                'type' => 'nested',
                'children' => buildDiff($firstObj->$key, $secondObj->$key)
            ];
        }
        if ($firstObj->$key !== $secondObj->$key) {
            return [
                'name' => $key,
                'type' => 'changed',
                'valueBefore' => $firstObj->$key,
                'valueAfter' => $secondObj->$key
            ];
        }
        return [
            'name' => $key,
            'type' => 'unchanged',
            'value' => $firstObj->$key
        ];
    }, $sortedKeys);
}

/**
 * @throws Exception
 */
function getFileData(string $filePath): object
{
    if (!file_exists($filePath)) {
        throw new Exception("File not found: {$filePath}");
    }

    $format = pathinfo($filePath, PATHINFO_EXTENSION);

    $data = file_get_contents($filePath);

    return parseData($format, $data);
}
