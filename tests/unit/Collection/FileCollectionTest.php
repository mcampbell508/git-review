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

namespace GitReview\Tests\Unit\Collection;

use GitReview\Collection\FileCollection;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class FileCollectionTest extends TestCase
{
    protected $collection;

    public function setUp(): void
    {
        $this->collection = new FileCollection();
    }

    public function testValidateWithValidObject(): void
    {
        $object = Mockery::mock('GitReview\File\FileInterface');

        $this->assertTrue($this->collection->validate($object));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateWithInvalidObject(): void
    {
        $object = 'Test';

        $this->collection->validate($object);
    }

    public function testSelectWithTrueCallback(): void
    {
        $file = Mockery::mock('GitReview\File\FileInterface');

        $this->collection->append($file);

        $filter = function () {
            return true;
        };

        $files = $this->collection->select($filter);

        $this->assertCount(1, $files);
    }

    public function testSelectWithFalseCallback(): void
    {
        $file = Mockery::mock('GitReview\File\FileInterface');

        $this->collection->append($file);

        $filter = function () {
            return false;
        };

        $files = $this->collection->select($filter);

        $this->assertCount(0, $files);
    }

    public function testSelectWithEmptyCollection(): void
    {
        $filter = function () {
            return true;
        };

        $this->assertEquals(new FileCollection(), $this->collection->select($filter));
    }
}
