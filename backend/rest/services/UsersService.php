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

        if (!$isUpdate) {
            if (empty($data['password']) || strlen($data['password']) < 3) {
                throw new InvalidArgumentException('Invalid password');
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
        $data = $this->validatePayload((array)$entity, false);

        /** @var UsersDao $dao */
        $dao = $this->dao;
        if ($dao->emailExists($data['email'])) {
            throw new InvalidArgumentException('Email already exists');
        }

        $ok = $this->dao->insert($data);
        if (!$ok) {
            throw new RuntimeException('Failed to create user');
        }

        $created = $dao->getByEmail($data['email']);
        return $this->sanitize($created ?: $data);
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
