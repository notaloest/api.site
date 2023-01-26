<?php

declare(strict_types=1);

namespace App;

use http\Exception\InvalidArgumentException;
use PDO;
use PDOException;
class Database
{
    /**
     * @var PDO
     */
    private PDO $connect;

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     */

    public function __construct(string $dsn, string $username = '', string $password = '')
    {
        try {
            $this->connect = new PDO($dsn, $username, $password);
        } catch (PDOException $exception) {
            throw new InvalidArgumentException('Error connect database' . $exception->getMessage());
        }
        $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connect;
    }
}