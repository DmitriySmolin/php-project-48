<?php

namespace Php\Package\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use function Gen\Diff\genDiff;
use function Gen\Diff\makeStylishString;
use function Gen\Diff\parseFile;

class FormatterTest extends TestCase
{
    private $expected;

    public function setUp(): void
    {
        $this->expected = <<<HEREDOC
{
 - follow: false
   host: hexlet.io
 - proxy: 123.234.53.22
 - timeout: 50
 + timeout: 20
 + verbose: true
}

HEREDOC;

    }

    /**
     * @throws Exception
     */
    public function testMakeStylishJson()
    {
        $firstArray = parseFile('tests/fixtures/json/file1.json');
        $secondArray = parseFile('tests/fixtures/json/file2.json');

        $diff = genDiff($firstArray, $secondArray);
        $actual = makeStylishString($diff);

        $this->assertEquals($this->expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testMakeStylishYaml()
    {
        $firstArray = parseFile('tests/fixtures/yaml/file1.yaml');
        $secondArray = parseFile('tests/fixtures/yaml/file2.yaml');

        $diff = genDiff($firstArray, $secondArray);
        $actual = makeStylishString($diff);

        $this->assertEquals($this->expected, $actual);
    }

}
