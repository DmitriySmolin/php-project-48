<?php

namespace Differ\Parsers;

use Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * @throws Exception
 */
function parseData(array $fileData): array|object|null
{
    [$format, $data] = $fileData;

    return match ($format) {
        'json' => json_decode($data),
        'yaml', 'yml' => Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP),
        'default' => throw new Exception("Format '$format' is not supported!")
    };
}
