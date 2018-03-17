<?php

namespace GitReview\Test\Functional;

use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Process\Process;

class FunctionalTestCase extends TestCase
{
    protected $directory;
    protected $testFileName;

    public function setUp()
    {
        $this->directory  = sys_get_temp_dir() . '/git-review-functional-tests/';

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        } else {
            // Clean up any created files.
            $this->runProcess('rm -rf ' . $this->directory . DIRECTORY_SEPARATOR . '*');
        }

        $this->directory = realpath($this->directory);
        $this->testFileName = 'test.txt';

        chdir($this->directory);
    }

    public function tearDown()
    {
        // Clean up any created files.
        $this->runProcess('rm -rf ' . $this->directory);
    }

    /**
     * @param $command
     *
     * @return Process
     */
    protected function runProcess($command): Process
    {
        $process = new Process($command, $this->directory);
        $process->run();

        return $process;
    }
}
