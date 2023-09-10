<?php

namespace Differ\Parsers;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

/**
 * @throws Exception
 */
function parseFile($filePath)
{
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    if ($extension === 'json') {
        return parseJson($filePath);
    }

    if (in_array($extension, ['yaml', 'yml'])) {
        return parseYaml($filePath);
    }

    throw new Exception("Format $extension is not supported!");
}

function parseJson(string $filePath)
{
    if (!file_exists($filePath)) {
        throw new InvalidArgumentException("File not found: {$filePath}");
    }

    return json_decode(file_get_contents($filePath), true);
}

function parseYaml(string $filePath): array
{
    if (!file_exists($filePath)) {
        throw new InvalidArgumentException("File not found: {$filePath}");
    }

    return (array) Yaml::parse(file_get_contents($filePath));
}
