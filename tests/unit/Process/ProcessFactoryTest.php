<?php

namespace GitReview\Tests\Unit\Process;

use GitReview\Process\ProcessFactory;
use GitReview\Process\ProcessInterface;
use PHPUnit\Framework\TestCase;

class ProcessFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_process_through_factory(): void
    {
        $this->assertInstanceOf(ProcessInterface::class, (new ProcessFactory())->create('command'));
    }
}
