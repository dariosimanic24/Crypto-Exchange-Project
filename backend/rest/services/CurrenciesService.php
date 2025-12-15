<?php
require_once __DIR__ . '/../dao/CurrenciesDao.php';
require_once __DIR__ . '/BaseService.php';

class CurrenciesService extends BaseService {

    public function __construct() {
        parent::__construct(new CurrenciesDao());
    }

    private function validatePayload(array $data, bool $isUpdate = false): array {
        $out = [];

        if (isset($data['code']) || !$isUpdate) {
            $code = isset($data['code']) ? strtoupper(trim($data['code'])) : '';
            if ($code === '' || !preg_match('/^[A-Z0-9]{2,10}$/', $code)) {
                throw new InvalidArgumentException('Invalid currency code');
            }
            $out['code'] = $code;
        }

        if (isset($data['name']) || !$isUpdate) {
            $name = isset($data['name']) ? trim($data['name']) : '';
            if ($name === '' || mb_strlen($name) > 60) {
                throw new InvalidArgumentException('Invalid currency name');
            }
            $out['name'] = $name;
        }

        if (isset($data['decimals']) || !$isUpdate) {
            if (!isset($data['decimals']) || !is_numeric($data['decimals'])) {
                throw new InvalidArgumentException('Invalid decimals');
            }
            $dec = (int)$data['decimals'];
            if ($dec < 0 || $dec > 18) {
                throw new InvalidArgumentException('Decimals must be between 0 and 18');
            }
            $out['decimals'] = $dec;
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
        if (!$row) {
            throw new InvalidArgumentException('Currency not found');
        }
        return $this->sanitize($row);
    }

    public function add($entity) {
        $data = $this->validatePayload((array)$entity, false);

        if ($this->dao->existsByCode($data['code'])) {
            throw new InvalidArgumentException('Currency code already exists');
        }

        $ok = parent::add($data);
        if (!$ok) {
            throw new RuntimeException('Failed to create currency');
        }

        $created = $this->dao->getByCode($data['code']);
        return $this->sanitize($created ?: $data);
    }

    public function update($entity, $id, $id_column = "id") {
        $this->get_by_id($id);

        $data = $this->validatePayload((array)$entity, true);
        $ok = parent::update($data, $id, $id_column);
        if (!$ok) {
            throw new RuntimeException('Failed to update currency');
        }

        return $this->get_by_id($id);
    }

    public function delete($id) {
        $this->get_by_id($id);
        try {
            return parent::delete($id);
        } catch (Throwable $e) {
            throw new RuntimeException('Cannot delete currency that is in use');
        }
    }
}
