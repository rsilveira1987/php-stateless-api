<?php

    namespace App\Utils;

    final class SQLTransaction
    {
        // connection instance
        private static $conn;

        private function __construct(){}

        public static function open($iniFile)
        {
            self::$conn = SQLConnection::open($iniFile);
			self::$conn->beginTransaction();
        }

        public static function getInstance()
        {
            return self::$conn;
        }

        public static function rollback()
        {
            if (!empty(self::$conn)) {
                self::$conn->rollback();
                self::$conn = null;
            }
        }

        public static function close()
        {
            if (!empty(self::$conn)) {
                self::$conn->commit();
                self::$conn = null;
            }
        }

    }

    class SQLTransactionException extends \Exception { }