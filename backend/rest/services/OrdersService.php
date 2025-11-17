<?php
require_once __DIR__ . '/../dao/OrdersDao.php';
require_once __DIR__ . '/BaseService.php';

class OrdersService extends BaseService {

    public function __construct() {
        parent::__construct(new OrdersDao());
    }

    private function validatePayload(array $data, bool $isUpdate = false): array {
        $out = [];

        if (isset($data['user_id']) || !$isUpdate) {
            if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
                throw new InvalidArgumentException('Invalid user_id');
            }
            $out['user_id'] = (int)$data['user_id'];
        }

        if (isset($data['base_currency_id']) || !$isUpdate) {
            if (!isset($data['base_currency_id']) || !is_numeric($data['base_currency_id'])) {
                throw new InvalidArgumentException('Invalid base_currency_id');
            }
            $out['base_currency_id'] = (int)$data['base_currency_id'];
        }

        if (isset($data['quote_currency_id']) || !$isUpdate) {
            if (!isset($data['quote_currency_id']) || !is_numeric($data['quote_currency_id'])) {
                throw new InvalidArgumentException('Invalid quote_currency_id');
            }
            $out['quote_currency_id'] = (int)$data['quote_currency_id'];
        }

        if (isset($data['side']) || !$isUpdate) {
            $side = isset($data['side']) ? strtoupper(trim($data['side'])) : '';
            if (!in_array($side, ['BUY', 'SELL'], true)) {
                throw new InvalidArgumentException('Invalid side');
            }
            $out['side'] = $side;
        }

        if (isset($data['price']) || !$isUpdate) {
            if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
                throw new InvalidArgumentException('Invalid price');
            }
            $out['price'] = (float)$data['price'];
        }

        if (isset($data['amount']) || !$isUpdate) {
            if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
                throw new InvalidArgumentException('Invalid amount');
            }
            $out['amount'] = (float)$data['amount'];
        }

        if (isset($data['status']) || $isUpdate) {
            $status = isset($data['status']) ? strtoupper(trim($data['status'])) : 'OPEN';
            if (!in_array($status, ['OPEN', 'FILLED', 'CANCELLED'], true)) {
                throw new InvalidArgumentException('Invalid status');
            }
            $out['status'] = $status;
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
    if ($row === false || $row === null) {
        throw new InvalidArgumentException('Resource not found');
    }
    return $this->sanitize($row);
}


    public function add($entity) {
        $data = $this->validatePayload((array)$entity, false);
        $ok = $this->dao->insert($data);
        if (!$ok) {
            throw new RuntimeException('Failed to create order');
        }
        return $data;
    }

    public function update($entity, $id, $id_column = "id") {
        $data = $this->validatePayload((array)$entity, true);
        $ok = $this->dao->update($id, $data);
        if (!$ok) {
            throw new RuntimeException('Failed to update order');
        }
        return $this->get_by_id($id);
    }

    public function delete($id) {
        return parent::delete($id);
    }
}
