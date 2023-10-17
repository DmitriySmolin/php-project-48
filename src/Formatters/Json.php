<?php

namespace Differ\Formatters\Json;

use JsonException;

/**
 * @throws JsonException
 */
function render(array $tree): string
{
    return json_encode($tree, JSON_THROW_ON_ERROR);
}
