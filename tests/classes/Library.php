<?php
/**
 * User: andreas
 * Date: 2011-12-21
 * Time: 16:36
 */
class Library
{
    private $database;
    public function __construct(BookDatabase $BookDatabase){
        $this->database=$BookDatabase;
    }
    public function getDatabase(){
        return $this->database;
    }
}

class Library2{
    private $database;
    public function __construct(ITestDatabase $idatabase){
        $this->database=$idatabase;
    }
    public function getDatabase(){
        return $this->database;
    }

}