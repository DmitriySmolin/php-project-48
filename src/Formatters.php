<?php

namespace Differ\Formatters;

use Exception;

use function Differ\Formatters\Stylish\renderStylish;
use function Differ\Formatters\Plain\renderPlain;

/**
 * @throws Exception
 */
function formatRecords(array $records, string $formatName): string
{
    return match ($formatName) {
        'stylish' => renderStylish($records),
        'plain' => renderPlain($records),
        default => throw new \Exception("The '$formatName' format is unknown"),
    };
}
