<?php

namespace Differ\Parsers;

use Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * @throws Exception
 */
function parseData(array $fileData): array
{
    [$format, $data] = $fileData;

    $supportedFormats = ['json', 'yaml', 'yml'];

    if (!in_array($format, $supportedFormats, true)) {
        throw new Exception("Format '$format' is not supported!");
    }

    return match ($format) {
        'json' => json_decode($data, true),
        'yaml', 'yml' => (array)Yaml::parse($data),
    };
}
