<?php

namespace Differ\Differ;

use Exception;

use InvalidArgumentException;
use function Differ\Formatters\formatRecords;
use function Differ\Parsers\parseData;

/**
 * @throws Exception
 */
function genDiff(string $firstPath, string $secondPath, string $formatName = 'stylish'): string
{
    $firstObj = parseData(getFileData($firstPath));
    $secondObj = parseData(getFileData($secondPath));
    $diffTree = buildDiff($firstObj, $secondObj);
    return formatRecords($diffTree, $formatName);
}

function buildDiff(object $firstObj, object $secondObj): array
{
    $mergedKeys = array_merge(
        array_keys(get_object_vars($secondObj)),
        array_keys(get_object_vars($firstObj))
    );

    $sortedUniqueKeys = array_values(array_unique($mergedKeys));
    sort($sortedUniqueKeys);

    return array_map(function (mixed $key) use ($firstObj, $secondObj) {
        if (!is_object($firstObj) || !is_object($secondObj)) {
            throw new InvalidArgumentException('Expected an object for $firstObj and $secondObj');
        }
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
    }, $sortedUniqueKeys);
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
