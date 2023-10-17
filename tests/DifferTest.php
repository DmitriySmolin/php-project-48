<?php

namespace Php\Package\Tests;

use Exception;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    /**
     * @dataProvider providerTestMakeRecursive
     * @param string $format
     * @param string $fileType
     * @throws Exception
     */
    public function testMakeRecursive(string $format, string $fileType)
    {
        $firstFilePath = "tests/fixtures/recursive/{$fileType}/file1.{$fileType}";
        $secondFilePath = "tests/fixtures/recursive/{$fileType}/file2.{$fileType}";
        $expectedFilePath = "tests/fixtures/diff.{$format}";
        $actualResult = genDiff($firstFilePath, $secondFilePath, $format);
        $this->assertStringEqualsFile($expectedFilePath, $actualResult);
    }

    public static function providerTestMakeRecursive(): array
    {
        return [
            ['stylish', 'json'],
            ['stylish', 'yaml'],
            ['plain', 'json'],
            ['plain', 'yaml'],
            ['json', 'json'],
            ['json', 'yaml'],
        ];
    }
}
