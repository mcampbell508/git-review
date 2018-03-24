<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2014 Samuel Parkinson <@samparkinson_>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE
 */

namespace GitReview\Tests\Unit\Classes;

use GitReview\File\File;
use PHPUnit_Framework_TestCase as TestCase;

class FileTest extends TestCase
{
    protected $fileStatus;

    protected $filePath;

    protected $projectPath;

    protected $file;

    public function setUp(): void
    {
        $this->fileStatus = 'M';
        $this->filePath = __FILE__;
        $this->projectPath = __DIR__;

        $this->file = new File($this->fileStatus, $this->filePath, $this->projectPath);

        $this->assertNotNull($this->file);
    }

    public function testGetFileName(): void
    {
        $expected = \basename($this->filePath);

        $this->assertSame($expected, $this->file->getFileName());
    }

    public function testGetRelativePath(): void
    {
        $expected = \str_replace($this->projectPath . DIRECTORY_SEPARATOR, '', $this->filePath);

        $this->assertSame($expected, $this->file->getRelativePath());
    }

    public function testGetFullPathWithNoCachedPath(): void
    {
        $this->assertSame($this->filePath, $this->file->getFullPath());
    }

    public function testGetFullPathWithCachedPath(): void
    {
        $path = __FILE__;

        $this->file->setCachedPath($path);

        $this->assertSame($path, $this->file->getFullPath());
    }

    public function testGetCachedPath(): void
    {
        $this->assertNull($this->file->getCachedPath());

        $path = __DIR__;

        $result = $this->file->setCachedPath($path);

        $this->assertSame($path, $this->file->getCachedPath());
    }

    public function testSetCachedPath(): void
    {
        $this->assertNull($this->file->getCachedPath());

        $path = __DIR__;

        $this->assertSame($this->file, $this->file->setCachedPath($path));

        $this->assertSame($path, $this->file->getCachedPath());
    }

    public function testGetExtension(): void
    {
        $expected = \pathinfo($this->filePath, PATHINFO_EXTENSION);

        $this->assertSame($expected, $this->file->getExtension());
    }

    public function testGetStatus(): void
    {
        $this->assertSame($this->fileStatus, $this->file->getStatus());
    }

    public function testGetFormattedStatus(): void
    {
        $statuses = ['A', 'C', 'M', 'R'];

        foreach ($statuses as $status) {
            $file = new File($status, $this->filePath, $this->projectPath);
            $this->assertInternalType('string', $file->getFormattedStatus());
        }
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testGetLevelNameWithInvalidInput(): void
    {
        $file = new File('Z', $this->filePath, $this->projectPath);

        $this->assertInstanceOf('\UnexpectedValueException', $file->getFormattedStatus());
    }

    public function testGetMimeType(): void
    {
        $this->assertTrue(\mb_strpos($this->file->getMimeType(), 'php') !== false);
    }
}
