<?php
require_once __DIR__ . '/../dao/WalletsDao.php';
require_once __DIR__ . '/BaseService.php';

class WalletsService extends BaseService {

    public function __construct() {
        parent::__construct(new WalletsDao());
    }

    private function validatePayload(array $data, bool $isUpdate = false): array {
        $out = [];

        if (isset($data['user_id']) || !$isUpdate) {
            if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
                throw new InvalidArgumentException('Invalid user_id');
            }
            $out['user_id'] = (int)$data['user_id'];
        }

        if (isset($data['currency_id']) || !$isUpdate) {
            if (!isset($data['currency_id']) || !is_numeric($data['currency_id'])) {
                throw new InvalidArgumentException('Invalid currency_id');
            }
            $out['currency_id'] = (int)$data['currency_id'];
        }

        if (isset($data['balance']) || !$isUpdate) {
            if (!isset($data['balance']) || !is_numeric($data['balance'])) {
                throw new InvalidArgumentException('Invalid balance');
            }
            $bal = (float)$data['balance'];
            if ($bal < 0) {
                throw new InvalidArgumentException('Balance cannot be negative');
            }
            $out['balance'] = $bal;
        }

        return $out;
    }

    private function sanitize(?array $row) {
    if ($row === null) {
        return null;
    }
    return $row;
}


    public function get_all() {
        $rows = parent::get_all();
        return array_map(fn($r) => $this->sanitize($r), $rows);
    }

    public function get_by_id($id) {
    $row = parent::get_by_id($id);
    if ($row === false || $row === null) {
        throw new InvalidArgumentException('Resource not found');
    }
    return $this->sanitize($row);
}


    public function add($entity) {
        $data = $this->validatePayload((array)$entity, false);
        $ok = $this->dao->insert($data);
        if (!$ok) {
            throw new RuntimeException('Failed to create wallet');
        }
        return $data;
    }

    public function update($entity, $id, $id_column = "id") {
        $data = $this->validatePayload((array)$entity, true);
        $ok = $this->dao->update($id, $data);
        if (!$ok) {
            throw new RuntimeException('Failed to update wallet');
        }
        return $this->get_by_id($id);
    }

    public function delete($id) {
        return parent::delete($id);
    }
}
