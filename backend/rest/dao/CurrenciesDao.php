<?php
require_once(__DIR__ . '/BaseDao.php');

class CurrenciesDao extends BaseDao {

    public function __construct() {
        parent::__construct('currencies');
    }

    public function getByCode($code) {
        $stmt = $this->connection->prepare("SELECT * FROM currencies WHERE code = :code");
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function existsByCode($code) {
        $stmt = $this->connection->prepare("SELECT 1 FROM currencies WHERE code = :code LIMIT 1");
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function listAllSorted() {
        $stmt = $this->connection->prepare("SELECT * FROM currencies ORDER BY code ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
