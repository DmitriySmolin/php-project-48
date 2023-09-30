<?php

namespace Php\Package\Tests;

use Exception;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class FormatterTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testMakeStylishRecursiveJson()
    {
        $firstArray = 'tests/fixtures/recursive/json/file1.json';
        $secondArray = 'tests/fixtures/recursive/json/file2.json';
        $expectedStylish = file_get_contents('tests/fixtures/diff.stylish');
        $actual = genDiff($firstArray, $secondArray);
        $this->assertEquals($expectedStylish, $actual);
    }

    /**
     * @throws Exception
     */
    public function testMakeStylishRecursiveYaml()
    {
        $firstArray = 'tests/fixtures/recursive/yaml/file1.yaml';
        $secondArray = 'tests/fixtures/recursive/yaml/file2.yaml';
        $expectedStylish = file_get_contents('tests/fixtures/diff.stylish');
        $actual = genDiff($firstArray, $secondArray);
        $this->assertEquals($expectedStylish, $actual);
    }

    /**
     * @throws Exception
     */
    public function testMakePlainRecursiveJson()
    {
        $firstArray = 'tests/fixtures/recursive/json/file1.json';
        $secondArray = 'tests/fixtures/recursive/json/file2.json';
        $expectedPlain = file_get_contents('tests/fixtures/diff.plain');
        $actual = genDiff($firstArray, $secondArray, 'plain');
        $this->assertEquals($expectedPlain, $actual);
    }

    /**
     * @throws Exception
     */
    public function testMakePlainRecursiveYaml()
    {
        $firstArray = 'tests/fixtures/recursive/yaml/file1.yaml';
        $secondArray = 'tests/fixtures/recursive/yaml/file2.yaml';
        $expectedPlain = file_get_contents('tests/fixtures/diff.plain');
        $actual = genDiff($firstArray, $secondArray, 'plain');
        $this->assertEquals($expectedPlain, $actual);
    }

    public function testMakeJsonRecursiveJson()
    {
        $firstArray = 'tests/fixtures/recursive/json/file1.json';
        $secondArray = 'tests/fixtures/recursive/json/file2.json';
        $expectedJson = file_get_contents('tests/fixtures/diff.json');
        $actual = genDiff($firstArray, $secondArray, 'json');
        $this->assertEquals($expectedJson, $actual);
    }

    /**
     * @throws Exception
     */
    public function testMakeJsonRecursiveYaml()
    {
        $firstArray = 'tests/fixtures/recursive/yaml/file1.yaml';
        $secondArray = 'tests/fixtures/recursive/yaml/file2.yaml';
        $expectedJson = file_get_contents('tests/fixtures/diff.json');
        $actual = genDiff($firstArray, $secondArray, 'json');
        $this->assertEquals($expectedJson, $actual);
    }
}
