<?php

namespace Gen\Diff;

use Exception;

use function cli\line;
use function Funct\Collection\pluck;

/**
 * @throws Exception
 */
function printDiff(string $first, string $second, string $format = 'stylish'): void
{
    $firstArray = parseFile($first);
    $secondArray = parseFile($second);

    $oldSign = '-';
    $newSign = '+';
    $sameSign = ' ';
    $differences = genDiff($firstArray, $secondArray);

    ksort($differences);

    line('{');

    foreach ($differences as $property => $change) {
        $status = $change['type'];

        match ($status) {
            'added' => line(" %s %s: %s", $newSign, $property, convertValueToString($change['actual'])),
            'deleted' => line(" %s %s: %s", $oldSign, $property, convertValueToString($change['old'])),
            'changed' => [
                line(" %s %s: %s", $oldSign, $property, convertValueToString($change['old'])),
                line(" %s %s: %s", $newSign, $property, convertValueToString($change['actual']))
            ],
            'same' => line(" %s %s: %s", $sameSign, $property, convertValueToString($change['actual'])),
            default => throw new Exception('Invalid diff status'),
        };
    }

    line('}');
}

function convertValueToString($value): string
{
    return match (true) {
        $value === true => 'true',
        $value === false => 'false',
        default => $value
    };
}

function genDiff(array $first, array $second): array
{
    $merged = [...$first, ...$second];

    $plucked = [];

    foreach ($merged as $key => $value) {
        $plucked[$key] = pluck([$merged, $first, $second], $key);
    }

    $mapped = [];
    foreach ($plucked as $key => $value) {
        [, $first, $second] = $value;

        $comparisonState = match (true) {
            is_null($first) && !is_null($second) => 'added',
            !is_null($first) && is_null($second) => 'deleted',
            $first === $second => 'same',
            default => 'changed',
        };

        $mapped[$key] = [
            'old' => $first,
            'actual' => $second,
            'type' => $comparisonState
        ];
    }

    return $mapped;
}
