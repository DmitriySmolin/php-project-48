<?php

namespace Php\Package\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use function Gen\Diff\genDiff;
use function Gen\Diff\parseFile;

class GenDiffTest extends TestCase
{
    private $expected;

    public function setUp(): void
    {
        $this->expected = [
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
    }

    /**
     * @throws Exception
     */
    public function testGenDiffJson()
    {
        $firstArray = parseFile('tests/fixtures/json/file1.json');
        $secondArray = parseFile('tests/fixtures/json/file2.json');

        $actual = genDiff($firstArray, $secondArray);

        $this->assertEquals($this->expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testGenDiffYaml()
    {
        $firstArray = parseFile('tests/fixtures/yaml/file1.yaml');
        $secondArray = parseFile('tests/fixtures/yaml/file2.yaml');
        $actual = genDiff($firstArray, $secondArray);

        $this->assertEquals($this->expected, $actual);
    }

}
