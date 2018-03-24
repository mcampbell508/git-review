<?php

namespace Tests\Unit\Framework\Process;

use GitReview\Process\ProcessFactory;
use GitReview\Process\ProcessInterface;
use PHPUnit_Framework_TestCase as TestCase;

class ProcessFactoryTest extends TestCase
{
    public function testItCanCreateProcessThroughFactory(): void
    {
        $this->assertInstanceOf(ProcessInterface::class, (new ProcessFactory())->create('command'));
    }
}
