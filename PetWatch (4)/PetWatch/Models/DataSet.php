<?php
require_once('Database.php');

// class related to data set
class DataSet {
    protected $_dbHandle, $_dbInstance;

    // this connects to database
    public function __construct() {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }

    // this helps runs queries
    protected function fetchData($sqlQuery, $params = array()) {
        $statement = $this->_dbHandle->prepare($sqlQuery);
        if (!empty($params)) {
            $statement->execute($params);
        } else {
            $statement->execute();
        }
        return $statement;
    }
}
?>
