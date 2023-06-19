<?php

    namespace App\Utils;

    class SlimFlash 
    {
        protected $message;
        protected $container;
        protected $storageKey = 'slimFlash';

        public function __construct($container) {
            $this->container = $container;
            $this->message = null;
        }

        public function getMessage() {
            $message = $this->message;
            return $message;
        }

        public function setMessage($message) {
            $this->message = $message;    
        }

    }