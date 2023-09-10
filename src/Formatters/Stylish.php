<?php

namespace Differ\Formatters\Stylish;

use function Functional\pick;

const PLUS = '+ ';
const MINUS = '- ';
const EMPTY_TAG = '  ';
const LENGTH_OF_TAGS = 2;
const INDENT_LENGTH = 4;

function renderStylish(array $node): string
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

    return ($tags[pick($node, 'type')]);
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
