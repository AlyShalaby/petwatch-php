<?php
// pet information stored in this class
class PetData {
    protected $_id, $_name, $_species, $_breed, $_color, $_status, $_description, $_photo_url, $_user_id, $_date_reported;

    // this gets the data from database and puts it in the class
    public function __construct($dbRow) {
        $this->_id = $dbRow['id'];
        $this->_name = $dbRow['name'];
        $this->_species = $dbRow['species'];
        $this->_breed = $dbRow['breed'];
        $this->_color = $dbRow['color'];
        $this->_status = $dbRow['status'];
        $this->_description = $dbRow['description'] ?? '';
        $this->_photo_url = $dbRow['photo_url'] ?? '';
        $this->_user_id = $dbRow['user_id'] ?? '';
        $this->_date_reported = $dbRow['date_reported'] ?? '';
    }

    // methods to get the data
    public function getId() { return $this->_id; }
    public function getName() { return $this->_name; }
    public function getSpecies() { return $this->_species; }
    public function getBreed() { return $this->_breed; }
    public function getColor() { return $this->_color; }
    public function getStatus() { return $this->_status; }
    public function getDescription() { return $this->_description; }
    public function getPhotoUrl() { return $this->_photo_url; }
    public function getUserId() { return $this->_user_id; }
    public function getDateReported() { return $this->_date_reported; }
}
?>
