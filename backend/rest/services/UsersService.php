<?php
require_once __DIR__ . '/../dao/UsersDao.php';
require_once __DIR__ . '/BaseService.php';

class UsersService extends BaseService {

    public function __construct() {
        parent::__construct(new UsersDao());
    }

    private function sanitize(?array $row) {
        if (!$row) {
            return $row;
        }
        if (isset($row['password'])) {
            unset($row['password']);
        }
        return $row;
    }

    private function validatePayload(array $data, bool $isUpdate = false): array {
        $out = [];

        if (isset($data['name']) || !$isUpdate) {
            $name = isset($data['name']) ? trim($data['name']) : '';
            if ($name === '' || mb_strlen($name) > 120) {
                throw new InvalidArgumentException('Invalid name');
            }
            $out['name'] = $name;
        }

        if (isset($data['email']) || !$isUpdate) {
            $email = isset($data['email']) ? trim($data['email']) : '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email');
            }
            $out['email'] = $email;
        }

        // For CREATE only (not update) validate password
        if (!$isUpdate) {
            if (empty($data['password']) || strlen($data['password']) < 8) {
                throw new InvalidArgumentException('Password must be at least 8 characters');
            }
            $out['password'] = $data['password'];
        }

        return $out;
    }

    public function get_all() {
        $rows = parent::get_all();
        return array_map(fn($r) => $this->sanitize($r), $rows);
    }

    public function get_by_id($id) {
        $row = parent::get_by_id($id);
        return $this->sanitize($row);
    }

    public function add($entity) {
        // Pretvori u array da budemo sigurni
        $data = (array)$entity;

        // Trim inputs
        $data['name']  = isset($data['name']) ? trim($data['name']) : '';
        $data['email'] = isset($data['email']) ? trim($data['email']) : '';

        // Osnovna validacija required
        if ($data['name'] === '' || $data['email'] === '' || empty($data['password'])) {
            throw new InvalidArgumentException('Name, email and password are required');
        }

        // Email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }

        // Password length
        if (strlen($data['password']) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters');
        }

        // Duplicate email check
        if ($this->dao->getByEmail($data['email'])) {
            throw new InvalidArgumentException('User with this email already exists');
        }

        // HASH PASSWORDA
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // Insert
        $ok = $this->dao->insert($data);

        if (!$ok) {
            throw new RuntimeException('Failed to create user');
        }

        // Return created user (without password)
        $user = $this->dao->getByEmail($data['email']);
        if ($user && isset($user['password'])) {
            unset($user['password']);
        }

        return $user;
    }

    public function update($entity, $id, $id_column = "id") {
        $data = $this->validatePayload((array)$entity, true);

        $ok = $this->dao->update($id, $data);
        if (!$ok) {
            throw new RuntimeException('Failed to update user');
        }

        return $this->get_by_id($id);
    }

    public function delete($id) {
        return parent::delete($id);
    }
}
