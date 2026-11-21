<?php
require_once('DataSet.php');
require_once('PetData.php');

class PetsDataSet extends DataSet {

    // this gets all pets
    public function fetchAllPets() {
        $statement = $this->fetchData('SELECT * FROM pets;');
        $dataSet = array();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $dataSet[] = new PetData($row);
        }
        return $dataSet;
    }

    // this gets pets for one user
    public function fetchPetsByUserId($user_id) {
        $statement = $this->fetchData('SELECT * FROM pets WHERE user_id = ?;', array($user_id));
        $dataSet = array();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $dataSet[] = new PetData($row);
        }
        return $dataSet;
    }

    // this makes the search filters
    private function buildSearchConditions($name, $species, $breed, $color, $status, $description, $location, &$sqlQuery, &$params) {
        if (!empty($name)) {
            $sqlQuery .= ' AND p.name LIKE :name';
            $params[':name'] = '%' . $name . '%';
        }
        if (!empty($species)) {
            $sqlQuery .= ' AND p.species LIKE :species';
            $params[':species'] = '%' . $species . '%';
        }
        if (!empty($breed)) {
            $sqlQuery .= ' AND p.breed LIKE :breed';
            $params[':breed'] = '%' . $breed . '%';
        }
        if (!empty($color)) {
            $sqlQuery .= ' AND p.color LIKE :color';
            $params[':color'] = '%' . $color . '%';
        }
        if (!empty($status)) {
            $sqlQuery .= ' AND p.status = :status';
            $params[':status'] = $status;
        }
        if (!empty($description)) {
            $sqlQuery .= ' AND p.description LIKE :description';
            $params[':description'] = '%' . $description . '%';
        }
        if (!empty($location)) {
            $sqlQuery .= ' AND (p.description LIKE :location OR s.comment LIKE :location)';
            $params[':location'] = '%' . $location . '%';
        }
    }

    // this searches pets
    public function searchPets($name, $species, $breed, $color, $status, $description = '', $location = '', $limit = null, $offset = null) {
        $sqlQuery = 'SELECT DISTINCT p.id, p.name, p.species, p.breed, p.color, p.status, p.description, p.photo_url, p.user_id, p.date_reported FROM pets p';
        if (!empty($location)) {
            $sqlQuery .= ' LEFT JOIN sightings s ON p.id = s.pet_id';
        }
        $sqlQuery .= ' WHERE 1=1';
        $params = array();
        $this->buildSearchConditions($name, $species, $breed, $color, $status, $description, $location, $sqlQuery, $params);
        $sqlQuery .= ' ORDER BY p.date_reported DESC';

        if ($limit !== null && $offset !== null) {
            $sqlQuery .= ' LIMIT :limit OFFSET :offset';
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
        }

        $statement = $this->_dbHandle->prepare($sqlQuery);
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value, ($key == ':limit' || $key == ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->execute();

        $dataSet = array();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $dataSet[] = new PetData($row);
        }
        return $dataSet;
    }

    // this counts pets with search filters
    public function countPets($name = '', $species = '', $breed = '', $color = '', $status = '', $description = '', $location = '') {
        $sqlQuery = 'SELECT COUNT(DISTINCT p.id) as total FROM pets p';
        if (!empty($location)) {
            $sqlQuery .= ' LEFT JOIN sightings s ON p.id = s.pet_id';
        }
        $sqlQuery .= ' WHERE 1=1';
        $params = array();
        $this->buildSearchConditions($name, $species, $breed, $color, $status, $description, $location, $sqlQuery, $params);

        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute($params);
        return (int)$statement->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // this gets all pets in pages like 10 at a time
    public function fetchAllPetsPaginated($limit = null, $offset = null) {
        $sqlQuery = 'SELECT * FROM pets ORDER BY date_reported DESC';
        if ($limit !== null && $offset !== null) {
            $sqlQuery .= ' LIMIT :limit OFFSET :offset';
        }

        $statement = $this->_dbHandle->prepare($sqlQuery);
        if ($limit !== null && $offset !== null) {
            $statement->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        $statement->execute();

        $dataSet = array();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $dataSet[] = new PetData($row);
        }
        return $dataSet;
    }

    // this counts all pets without filters
    public function countAllPets() {
        $statement = $this->fetchData('SELECT COUNT(*) as total FROM pets');
        return (int)$statement->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // this gets the latest sighting that has coordinates for a pet
    public function fetchLatestSightingWithCoords($pet_id) {
        $statement = $this->fetchData(
            'SELECT comment, latitude, longitude FROM sightings WHERE pet_id = ? AND latitude IS NOT NULL AND longitude IS NOT NULL AND latitude != "" AND longitude != "" ORDER BY id DESC LIMIT 1',
            array($pet_id)
        );
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }
}
?>
