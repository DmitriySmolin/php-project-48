<?php

namespace Gen\Diff;

use Exception;
use Functional;
use function Functional\pick;

const PLUS = '+ ';
const MINUS = '- ';
const EMPTY_TAG = '  ';
const LENGTH_OF_TAGS = 2;
const INDENT_LENGTH = 4;

/**
 * @throws Exception
 */
function genDiff(string $firstPath, string $secondPath, string $format = 'stylish'): string
{
    $first = (array) parseFile($firstPath);
    $second = (array) parseFile($secondPath);
    $diffTree = buildDiffTree($first, $second);
    return stylish($diffTree);
}

function stylish(array $node): string
{
    $iter = function ($node, $depth) use (&$iter) {

        $itemIndent = buildIndent($depth, LENGTH_OF_TAGS);
        $bracketIndent = buildIndent($depth);

        $type = pick($node, 'type');
        $tag = getTag($node);

        switch ($type) {
            case 'root':
                $children = pick($node, 'children');
                $lines = array_map(
                    function ($node) use ($iter, $depth) {
                        return $iter($node, $depth);
                    },
                    $children
                );

                $result = ['{', ...$lines, '}'];
                return implode("\n", $result);

            case 'nested':
                $key = pick($node, 'key');
                $children = pick($node, 'children');

                $lines = array_map(
                    function ($node) use ($iter, $depth) {
                        return $iter($node, $depth + 1);
                    },
                    $children
                );

                $result = ["{$itemIndent}{$tag}{$key}: {", ...$lines, "{$bracketIndent}}"];
                return implode("\n", $result);

            case 'changed':
                $key = pick($node, 'key');

                [$tag1, $tag2] = explode('.', $tag);

                $renderedValue1 = stringify(pick($node, 'value1'), $depth + 1);
                $renderedValue2 = stringify(pick($node, 'value2'), $depth + 1);

                $first = "{$itemIndent}{$tag1}{$key}: {$renderedValue1}";
                $second = "{$itemIndent}{$tag2}{$key}: {$renderedValue2}";

                return implode("\n", [$first, $second]);

            case 'deleted':
            case 'added':
            case 'unchanged':
                $key = pick($node, 'key');
                $value = pick($node, 'value');

                $renderedValue = stringify($value, $depth + 1);

                return "{$itemIndent}{$tag}{$key}: {$renderedValue}";

            default:
                throw new \Exception("Unknown or not existed state");
        }
    };

    return $iter($node, 0);
}

function stringify(mixed $data, int $startDepth = 0): string
{

    $iter = function ($data, $depth) use (&$iter) {
        if (!is_array($data)) {
            return toString($data);
        }

        $itemIndent = buildIndent($depth);
        $bracketIndent = buildIndent($depth - 1);

        $lines = array_map(
            fn($key, $value) => "{$itemIndent}{$key}: {$iter($value, $depth + 1)}",
            array_keys($data),
            array_values($data)
        );

        $result = ['{', ...$lines, "{$bracketIndent}}"];
        return implode("\n", $result);
    };

    return $iter($data, $startDepth);
}

function getTag(array $node): string
{
    $tags = [
        'added' => PLUS,
        'deleted' => MINUS,
        'unchanged' => EMPTY_TAG,
        'nested' => EMPTY_TAG,
        'changed' => MINUS . '.' . PLUS,
        'root' => 'no tag',
    ];

    return($tags[pick($node, 'type')]);
}

function buildIndent(int $depthOfNode, int $lengthOfTag = 0): string
{
    $depthOfElement = $depthOfNode + 1;
    return str_repeat(' ', INDENT_LENGTH * $depthOfElement - $lengthOfTag);
}

function toString(mixed $input): string
{
    $exported = var_export($input, true) === 'NULL' ? 'null' : var_export($input, true);
    return trim($exported, "'");
}


function buildDiff(array $first, array $second): array
{
    $keys = array_unique(
        array_merge(
            array_keys($first),
            array_keys($second)
        )
    );

    $sortedKeys = Functional\sort($keys, fn ($left, $right) => strcmp($left, $right));

    return array_map(function ($key) use ($first, $second) {

        $value1 = $first[$key] ?? null;
        $value2 = $second[$key] ?? null;

        if (!array_key_exists($key, $first)) {
            return [
                'key' => $key,
                'type' => 'added',
                'value' => $value2,
            ];
        }

        if (!array_key_exists($key, $second)) {
            return [
                'key' => $key,
                'type' => 'deleted',
                'value' => $value1,
            ];
        }

        if (is_array($value1) && is_array($value2)) {
            return [
                'key' => $key,
                'type' => 'nested',
                'children' => buildDiff($value1, $value2),
            ];
        }

        if ($value1 === $value2) {
            return [
                'key' => $key,
                'type' => 'unchanged',
                'value' => $value1,
            ];
        }

        return [
            'key' => $key,
            'type' => 'changed',
            'value1' => $value1,
            'value2' => $value2,
        ];
    }, $sortedKeys);
}

function buildDiffTree(array $first, array $second): array
{
    return [
        'type' => 'root',
        'children' => buildDiff($first, $second),
    ];
}
