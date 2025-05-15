<?php

class Connect
{
  function env($key, $default = null)
  {
    $env = parse_ini_file(__DIR__ . '/../backend/.env');
    return $env[$key] ?? $default;
  }
  static public function connection($type = null)
  {
    // Database connection parameters and drivers 
    $driver = $type ?: self::env('DB_CONNECTION', 'mysql');
    try {
      switch ($driver) {
        case 'mysql':
        case 'mariadb':
          $dsn = "mysql:host=" . self::env('MYSQL_HOST') . ";port=" . self::env('MYSQL_PORT') . ";dbname=" . self::env('MYSQL_DATABASE');
          $user = self::env('MYSQL_USERNAME');
          $password = self::env('MYSQL_PASSWORD');
          break;

        case 'sqlsrv':
          $dsn = "sqlsrv:serverName=" . self::env('MSSQL_HOST')  . self::env('MSSQL_PORT');
          ";Database=" . self::env('MSSQL_DATABASE');
          $user = self::env('MSSQL_USERNAME');
          $password = self::env('MSSQL_PASSWORD');
          break;
        case 'pgsql':
          $dsn = "pgsql:host=" . self::env('POSTGRES_HOST') . ";port=" . self::env('POSTGRES_PORT') . ";dbname=" . self::env('POSTGRES_DATABASE');
          $user = self::env('POSTGRES_USERNAME');
          $password = self::env('POSTGRES_PASSWORD');
          break;

        default:
          throw new Exception("Tipo conexion '$driver' no es valido");
      }

      $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
      ];

      if ($driver === 'mysql' || $driver === 'mariadb') {
        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci';
      }

      return new PDO($dsn, $user, $password, $options);
    } catch (PDOException $e) {
      // Handle the exception
      echo "Connection failed: " . $e->getMessage();
      exit;
    }
  }
}
