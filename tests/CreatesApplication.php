<?php

namespace GitReview\Tests;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use LaravelZero\Framework\Application;
use LaravelZero\Framework\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application and returns it.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function createApplication(): ApplicationContract
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
