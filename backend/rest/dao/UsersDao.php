<?php
require_once(__DIR__ . '/BaseDao.php');

class UsersDao extends BaseDao {

    public function __construct() {
        parent::__construct('users');
    }

    public function getByEmail($email) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function emailExists($email) {
        $stmt = $this->connection->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function createUser($name, $email, $password) {
        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ];
        return $this->insert($data);
    }

    public function searchByName($term) {
        $like = "%" . $term . "%";
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE name LIKE :term");
        $stmt->bindParam(':term', $like);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
