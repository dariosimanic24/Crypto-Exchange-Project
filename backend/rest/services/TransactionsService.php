<?php
require_once __DIR__ . '/../dao/TransactionsDao.php';
require_once __DIR__ . '/BaseService.php';

class TransactionsService extends BaseService {

    public function __construct() {
        parent::__construct(new TransactionsDao());
    }

    private function validatePayload(array $data, bool $isUpdate = false): array {
        $out = [];

        if (isset($data['wallet_id']) || !$isUpdate) {
            if (!isset($data['wallet_id']) || !is_numeric($data['wallet_id'])) {
                throw new InvalidArgumentException('Invalid wallet_id');
            }
            $out['wallet_id'] = (int)$data['wallet_id'];
        }

        if (isset($data['type']) || !$isUpdate) {
            $type = isset($data['type']) ? strtoupper(trim($data['type'])) : '';
            $allowed = ['DEPOSIT', 'WITHDRAW', 'FILL_BUY', 'FILL_SELL'];
            if (!in_array($type, $allowed, true)) {
                throw new InvalidArgumentException('Invalid transaction type');
            }
            $out['type'] = $type;
        }

        if (isset($data['amount']) || !$isUpdate) {
            if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
                throw new InvalidArgumentException('Invalid amount');
            }
            $out['amount'] = (float)$data['amount'];
        }

        return $out;
    }

    private function sanitize(?array $row) {
        return $row;
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
        $ok = $this->dao->insert($data);
        if (!$ok) {
            throw new RuntimeException('Failed to create transaction');
        }
        return $data;
    }

    public function update($entity, $id, $id_column = "id") {
        $data = $this->validatePayload((array)$entity, true);
        $ok = $this->dao->update($id, $data);
        if (!$ok) {
            throw new RuntimeException('Failed to update transaction');
        }
        return $this->get_by_id($id);
    }

    public function delete($id) {
        return parent::delete($id);
    }
}
