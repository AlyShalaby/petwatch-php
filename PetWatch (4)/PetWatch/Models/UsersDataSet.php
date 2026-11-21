<?php
require_once('DataSet.php');
require_once('UserData.php');

// extends DataSet so we get database connection
class UsersDataSet extends DataSet {

    // get user by username for login
    public function fetchUser($username) {
        $sqlQuery = 'SELECT * FROM users WHERE username = :username LIMIT 1;';
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $row = $statement->fetch();

        if ($row) {
            return new UserData($row);
        }
        return null;
    }

    // get user by id if needed
    public function fetchUserByID($id) {
        $sqlQuery = 'SELECT * FROM users WHERE id = :id LIMIT 1;';
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $row = $statement->fetch();

        if ($row) {
            return new UserData($row);
        }
        return null;
    }
}
?>