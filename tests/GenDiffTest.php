<?php

namespace Php\Package\Tests;

use PHPUnit\Framework\TestCase;
use function Differ\genDiff;

class GenDiffTest extends TestCase
{
    public function testGenDiff()
    {
        $firstArray = json_decode(file_get_contents('tests/fixtures/examples/file1.json'), true);
        $secondArray = json_decode(file_get_contents('tests/fixtures/examples/file2.json'), true);

        $expected = [
            'host' => [
                'old' => 'hexlet.io',
                'actual' => 'hexlet.io',
                'type' => 'same',
            ],
            'timeout' => [
                'old' => 50,
                'actual' => 20,
                'type' => 'changed',
            ],
            'proxy' => [
                'old' => '123.234.53.22',
                'actual' => null,
                'type' => 'deleted',
            ],
            'follow' => [
                'old' => false,
                'actual' => null,
                'type' => 'deleted',
            ],
            'verbose' => [
                'old' => null,
                'actual' => true,
                'type' => 'added',
            ],
        ];

        $actual = genDiff($firstArray, $secondArray);

        $this->assertEquals($expected, $actual);
    }

}
