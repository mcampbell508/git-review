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

    /**
     * @test
     */
    public function get_file_name(): void
    {
        $expected = \basename($this->filePath);

        $this->assertSame($expected, $this->file->getFileName());
    }

    /**
     * @test
     */
    public function get_relative_path(): void
    {
        $expected = \str_replace($this->projectPath . DIRECTORY_SEPARATOR, '', $this->filePath);

        $this->assertSame($expected, $this->file->getRelativePath());
    }

    /**
     * @test
     */
    public function get_full_path_with_no_cached_path(): void
    {
        $this->assertSame($this->filePath, $this->file->getFullPath());
    }

    /**
     * @test
     */
    public function get_full_path_with_cached_path(): void
    {
        $path = __FILE__;

        $this->file->setCachedPath($path);

        $this->assertSame($path, $this->file->getFullPath());
    }

    /**
     * @test
     */
    public function get_cached_path(): void
    {
        $this->assertNull($this->file->getCachedPath());

        $path = __DIR__;

        $result = $this->file->setCachedPath($path);

        $this->assertSame($path, $this->file->getCachedPath());
    }

    /**
     * @test
     */
    public function set_cached_path(): void
    {
        $this->assertNull($this->file->getCachedPath());

        $path = __DIR__;

        $this->assertSame($this->file, $this->file->setCachedPath($path));

        $this->assertSame($path, $this->file->getCachedPath());
    }

    /**
     * @test
     */
    public function get_extension(): void
    {
        $expected = \pathinfo($this->filePath, PATHINFO_EXTENSION);

        $this->assertSame($expected, $this->file->getExtension());
    }

    /**
     * @test
     */
    public function get_status(): void
    {
        $this->assertSame($this->fileStatus, $this->file->getStatus());
    }

    /**
     * @test
     */
    public function get_formatted_status(): void
    {
        $statuses = ['A', 'C', 'M', 'R'];

        foreach ($statuses as $status) {
            $file = new File($status, $this->filePath, $this->projectPath);
            $this->assertInternalType('string', $file->getFormattedStatus());
        }
    }

    /**
     * @expectedException UnexpectedValueException
     *
     * @test
     */
    public function get_level_name_with_invalid_input(): void
    {
        $file = new File('Z', $this->filePath, $this->projectPath);

        $this->assertInstanceOf('\UnexpectedValueException', $file->getFormattedStatus());
    }

    /**
     * @test
     */
    public function get_mime_type(): void
    {
        $this->assertTrue(\mb_strpos($this->file->getMimeType(), 'php') !== false);
    }
}
