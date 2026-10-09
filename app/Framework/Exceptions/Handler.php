<?php

namespace App\Framework\Exceptions;

use Visualsoft\LaravelSecurity\Framework\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        parent::register();
    }
}
