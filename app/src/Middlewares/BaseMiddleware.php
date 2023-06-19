<?php

    namespace App\Middlewares;

    abstract class BaseMiddleware
    {
        protected $container;

        public function __construct($container) {
            $this->container = $container;
        }
    }