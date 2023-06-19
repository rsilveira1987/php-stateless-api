<?php

    namespace App\Utils;

    use \Exception;
    use \PDO;

    final class SQLConnection
    {
        public static function open($iniFile)
        {
            if (!file_exists($iniFile)) {
                throw new Exception("File {$iniFile} does not exist");
            }

            // read ini file
            $conf = parse_ini_file($iniFile);

            // grab db settings
            $user = isset($conf['user']) ? $conf['user'] : null;
            $pass = isset($conf['pass']) ? $conf['pass'] : null;
            $type = isset($conf['type']) ? $conf['type'] : null;
            $name = isset($conf['name']) ? $conf['name'] : null;
            $host = isset($conf['host']) ? $conf['host'] : null;
            $port = isset($conf['port']) ? $conf['port'] : null;

            // select db driver
            switch($type) {
                case 'mysql':$port = $port ? $port : '3306';
                    $conn = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass);
                    break;
                case 'pgsql':
                    $port = $port ? $port : '5432';
					$conn = new PDO("pgsql:dbname={$name}; user={$user}; password={$pass}; host={$host}; port={$port}");
                    break;
                case 'sqlite':
                    $sqliteFile = $name;
					$conn = new PDO("sqlite:{$sqliteFile}");
					break;
                default:
                    throw new Exception("Database driver {$type} not supported");
                    break;
            }
            // enable PDO exceptions
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        }
    }