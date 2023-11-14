<?php

namespace Integration\Helpers;

use Exception;
use PDO;
use PDOException;

class DB
{
    private PDO $pdo;
    private static DB $instance;
    private function __construct()
    {
        $dbhost = getenv("DB_HOST");
        $dbport = getenv("DB_PORT");
        $dbname = getenv("DB_NAME");
        $dbuser = getenv("DB_USER");
        $dbpass = getenv("DB_PASS");

        try {
            $this->pdo = new \PDO("pgsql:host=$dbhost;port=$dbport;dbname=$dbname", $dbuser, $dbpass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $exception) {
            throw new Exception("Ошибка при подключении к базе данных<br><b>{$exception->getMessage()}</b><br>");
        }
    }
    // передается название вызываемой функции и ее параметры
    static public function __callStatic($name, $arguments): mixed
    {
        if (!isset($instance))
            static::$instance = new DB();
        if (method_exists(static::$instance, $name))
            return static::$instance->$name(...$arguments);
        else return null;
    }

    private function query(string $query, array $params = []): ?array
    {
        $ex = $this->pdo->prepare($query);
        $ex->execute($params);
        return $ex->fetchAll(PDO::FETCH_ASSOC);
    }
}
