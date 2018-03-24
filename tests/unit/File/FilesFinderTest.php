<?php

namespace GitReview\Tests\Unit\Classes;

use GitReview\File\File;
use GitReview\File\FilesFinder;
use PHPUnit_Framework_TestCase as TestCase;
use Tightenco\Collect\Support\Collection;

class FilesFinderTest extends TestCase
{
    public function testGetFileCount(): void
    {
        $files = new Collection([
            new File('A', 'a/b/c.txt', '/tmp/repo-base'),
            new File('A', 'c/b/a.txt', '/tmp/repo-base'),
        ]);

        $foundFiles = new FilesFinder($files, ["a/b/c.txt"]);

        $this->assertEquals(1, $foundFiles->count());
    }

    public function testGetFoundFilesCanAccommodateWildcardDirectorySearch(): void
    {
        $files = new Collection([
            new File('A', 'example/test1/subfolder/file.txt', '/tmp/repo-base'),
            new File('A', 'example/test2/subfolder/test.js', '/tmp/repo-base'),
            new File('A', 'example/test2/subfolder/anotherlevel/test.php', '/tmp/repo-base'),
            new File('A', 'example/test3/legacy/file.txt', '/tmp/repo-base'),
            new File('A', 'example/test2/file.txt', '/tmp/repo-base'),
            new File('A', 'example2/another-file.txt', '/tmp/repo-base'),
            new File('A', '.php_cs.dist', '/tmp/repo-base'),
            new File('A', '.php_cs', '/tmp/repo-base'),
        ]);

        $foundFiles = new FilesFinder($files, [
            'example/*/subfolder',
            'example2/*',
            '.php_cs',
            '.php_cs.dist',
        ]);

        $this->assertEquals(6, $foundFiles->count());

        $this->assertEquals([
            'example/test1/subfolder/file.txt',
            'example/test2/subfolder/test.js',
            'example/test2/subfolder/anotherlevel/test.php',
            'example2/another-file.txt',
            '.php_cs.dist',
            '.php_cs',
        ], $foundFiles->getFoundFiles()->map(function (File $file) {
            return $file->getRelativePath();
        })->toArray());
    }
}
