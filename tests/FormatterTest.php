<?php

namespace Php\Package\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use function Gen\Diff\genDiff;
use function Gen\Diff\makeStylishString;
use function Gen\Diff\parseFile;

class FormatterTest extends TestCase
{
    private string $expected;

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
        $this->expected2 = <<<HEREDOC
{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            doge: {
              - wow: 
              + wow: so much
            }
            key: value
          + ops: vops
        }
    }
    group1: {
      - baz: bas
      + baz: bars
        foo: bar
      - nest: {
            key: value
        }
      + nest: str
    }
  - group2: {
        abc: 12345
        deep: {
            id: 45
        }
    }
  + group3: {
        deep: {
            id: {
                number: 45
            }
        }
        fee: 100500
    }
}
HEREDOC;
    }

    /**
     * @throws Exception
     */
    public function testMakeStylishJson()
    {
        $firstArray = 'tests/fixtures/json/file1.json';
        $secondArray = 'tests/fixtures/json/file2.json';

        $actual = genDiff($firstArray, $secondArray);
        $this->assertEquals($this->expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testMakeStylishRecursiveJson()
    {
        $firstArray = 'tests/fixtures/recursive/json/file1.json';
        $secondArray = 'tests/fixtures/recursive/json/file2.json';

        $actual = genDiff($firstArray, $secondArray);
        $this->assertEquals($this->expected2, $actual);
    }

    /**
     * @throws Exception
     */
    public function testMakeStylishYaml()
    {
        $firstArray = 'tests/fixtures/yaml/file1.yaml';
        $secondArray = 'tests/fixtures/yaml/file2.yaml';

        $actual = genDiff($firstArray, $secondArray);
        $this->assertEquals($this->expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testMakeStylishRecursiveYaml()
    {
        $firstArray = 'tests/fixtures/recursive/yaml/file1.yaml';
        $secondArray = 'tests/fixtures/recursive/yaml/file2.yaml';

        $actual = genDiff($firstArray, $secondArray);
        $this->assertEquals($this->expected2, $actual);
    }

}
