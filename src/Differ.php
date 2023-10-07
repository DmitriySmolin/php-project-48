<?php

namespace Differ\Differ;

use Exception;

use function Funct\Collection\sortBy;
use function Funct\Collection\union;
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


function buildDiff($firstObj, $secondObj): array
{
    $uniqueKeys = union(array_keys(get_object_vars($secondObj)), array_keys(get_object_vars($firstObj)));
    $sortedUniqueKeys = array_values(sortBy($uniqueKeys, function ($key) {
        return $key;
    }));
    return array_map(function ($key) use ($secondObj, $firstObj) {
        if (!property_exists($firstObj, $key)) {
            return [
                'name' => $key,
                'type' => 'removed',
                'value' => $secondObj->$key
            ];
        }
        if (!property_exists($secondObj, $key)) {
            return [
                'name' => $key,
                'type' => 'added',
                'value' => $firstObj->$key
            ];
        }
        if (is_object($secondObj->$key) && is_object($firstObj->$key)) {
            return [
                'name' => $key,
                'type' => 'nested',
                'children' => buildDiff($secondObj->$key, $firstObj->$key)
            ];
        }
        if ($secondObj->$key !== $firstObj->$key) {
            return [
                'name' => $key,
                'type' => 'changed',
                'value1' => $secondObj->$key,
                'value2' => $firstObj->$key
            ];
        }
        return [
            'name' => $key,
            'type' => 'unchanged',
            'value' => $secondObj->$key
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
