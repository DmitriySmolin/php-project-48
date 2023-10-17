<?php

namespace Differ\Formatters;

use Exception;
use  Differ\Formatters\Stylish;
use  Differ\Formatters\Plain;
use Differ\Formatters\Json;

/**
 * @throws Exception
 */
function formatRecords(array $records, string $formatName): string
{
    return match ($formatName) {
        'stylish' => Stylish\render($records),
        'plain' => Plain\render($records),
        'json' => Json\render($records),
        default => throw new \Exception("The '$formatName' format is unknown"),
    };
}
