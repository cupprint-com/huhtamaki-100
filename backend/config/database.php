<?php
# avoid this file from being accessed directly
if(!defined('CP_HUHTAMAKI_RUNNING')){
    exit(1);
}

require_once 'constants.php';
# Database class is used when we are not in a wordpress environment
class Database{
    
    public $conn;
    
    // get the database connection
    public function getConnection(){
        
        $this->conn = null;
        
        try{
            $this->conn = new PDO("mysql:host=" . H100_HOST . ";dbname=" . H100_DATABASE, H100_USERNAME, H100_PASSWORD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            $message=print_r($exception,true);
            error_log($message);
        }
        
        return $this->conn;
    }
}