<?php
require_once(__DIR__ . '/BaseDao.php');

class WalletsDao extends BaseDao {

    public function __construct() {
        parent::__construct('wallets');
    }

    public function getByUser($user_id) {
        $stmt = $this->connection->prepare("SELECT * FROM wallets WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUserAndCurrency($user_id, $currency_id) {
        $stmt = $this->connection->prepare("SELECT * FROM wallets WHERE user_id = :user_id AND currency_id = :currency_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':currency_id', $currency_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateBalance($wallet_id, $new_balance) {
        $stmt = $this->connection->prepare("UPDATE wallets SET balance = :balance WHERE id = :id");
        $stmt->bindParam(':balance', $new_balance);
        $stmt->bindParam(':id', $wallet_id);
        return $stmt->execute();
    }

    public function createWallet($user_id, $currency_id, $balance = 0) {
    $stmt = $this->connection->prepare("INSERT INTO wallets (user_id, currency_id, balance) VALUES (:u, :c, :b)");
    $stmt->bindParam(':u', $user_id);
    $stmt->bindParam(':c', $currency_id);
    $stmt->bindParam(':b', $balance);
    $stmt->execute();
    return (int)$this->connection->lastInsertId();
}

public function get_by_id($id) {
    $stmt = $this->connection->prepare("SELECT * FROM wallets WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch();
}

}
?>
