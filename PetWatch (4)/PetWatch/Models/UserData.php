<?php

//  interface
interface IUserData {
    public function getId();
    public function getUsername();
    public function getPasswordHash();
    public function getType();
}

// simple user data class used when logging in
class UserData implements IUserData
{
    private $_id;
    private $_username;
    private $_passwordHash;
    private $_type;

    // build the object from one row from the users table
    public function __construct($dbRow)
    {
        $this->_id = $dbRow['id'];
        $this->_username = $dbRow['username'];
        $this->_passwordHash = $dbRow['password_hash'];
        $this->_type = $dbRow['role'];
    }

    public function getId() { return $this->_id; }
    public function getUsername() { return $this->_username; }
    public function getPasswordHash() { return $this->_passwordHash; }
    public function getType() { return $this->_type; }
}
?>