<?php

namespace Classes;
use PDO;
use PDOException;

class Database
{
    private $host;
    private $dbname;
    private $user;
    private $pass;
    private $charset;


    public function __construct($host, $dbname, $user, $pass, $charset)
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->pass = $pass;
        $this->charset = $charset;
    }

    public function connection() {
        $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $db = new PDO($dsn, $this->user, $this->pass, $options);
            return $db;
        } catch (PDOException $e) {
            echo "Ошибка подключения к базе: " . $e->getMessage();
            exit;
        }
    }
}