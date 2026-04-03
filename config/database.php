<?php

class database
{
    public $conn;
    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "blog");
        if ($this->conn->connect_error) {
            die("Connection failed");
        }
    }

}