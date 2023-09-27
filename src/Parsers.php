<?php

namespace Differ\Parsers;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

/**
 * @throws Exception
 */
function parseData(array $dataFile)
{
    if (count($dataFile) < 2) {
        throw new InvalidArgumentException("Invalid input data: expected at least two elements");
    }

    [$format, $data] = $dataFile;

    return match ($format) {
        'json' => parseJson($data),
        'yaml', 'yml' => parseYaml($data),
        default => throw new Exception("Format $format is not supported!"),
    };
}

function parseJson(string $jsonData)
{
    return json_decode($jsonData, true);
}

function parseYaml(string $yamlData): array
{
    return (array)Yaml::parse($yamlData);
}
