<?php
require_once(__DIR__ . '/BaseDao.php');

class OrdersDao extends BaseDao {

    public function __construct() {
        parent::__construct('orders');
    }

    public function getByUser($user_id) {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOpenOrders() {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE status = 'OPEN' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByCurrencyPair($base_currency_id, $quote_currency_id) {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE base_currency_id = :base_currency_id AND quote_currency_id = :quote_currency_id ORDER BY created_at DESC");
        $stmt->bindParam(':base_currency_id', $base_currency_id);
        $stmt->bindParam(':quote_currency_id', $quote_currency_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
