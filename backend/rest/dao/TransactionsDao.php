<?php
require_once(__DIR__ . '/BaseDao.php');

class TransactionsDao extends BaseDao {

    public function __construct() {
        parent::__construct('transactions');
    }

    public function getByWallet($wallet_id) {
        $stmt = $this->connection->prepare("SELECT * FROM transactions WHERE wallet_id = :wallet_id ORDER BY created_at DESC");
        $stmt->bindParam(':wallet_id', $wallet_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecent($limit = 10) {
        $stmt = $this->connection->prepare("SELECT * FROM transactions ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByType($type) {
        $stmt = $this->connection->prepare("SELECT * FROM transactions WHERE type = :type ORDER BY created_at DESC");
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
