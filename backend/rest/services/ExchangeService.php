<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../dao/WalletsDao.php';
require_once __DIR__ . '/../dao/OrdersDao.php';
require_once __DIR__ . '/../dao/TransactionsDao.php';

class ExchangeService {

    private WalletsDao $walletsDao;
    private OrdersDao $ordersDao;
    private TransactionsDao $txDao;

    public function __construct() {
        $this->walletsDao = new WalletsDao();
        $this->ordersDao  = new OrdersDao();
        $this->txDao      = new TransactionsDao();
    }

    private function num($v, string $field): float {
        if (!isset($v) || !is_numeric($v)) {
            throw new InvalidArgumentException("Invalid {$field}");
        }
        return (float)$v;
    }

    public function execute(int $user_id, array $payload): array {
        $from_currency_id = (int)($payload['from_currency_id'] ?? 0);
        $to_currency_id   = (int)($payload['to_currency_id'] ?? 0);
        $amount = $this->num($payload['amount'] ?? null, "amount");
        $rate   = $this->num($payload['rate'] ?? null, "rate");

        if ($from_currency_id <= 0 || $to_currency_id <= 0) {
            throw new InvalidArgumentException("Invalid currency ids");
        }
        if ($from_currency_id === $to_currency_id) {
            throw new InvalidArgumentException("Currencies must differ");
        }
        if ($amount <= 0) throw new InvalidArgumentException("Amount must be > 0");
        if ($rate <= 0) throw new InvalidArgumentException("Rate must be > 0");

        // from wallet must exist
        $fromWallet = $this->walletsDao->getByUserAndCurrency($user_id, $from_currency_id);
        if (!$fromWallet) throw new InvalidArgumentException("From wallet not found");

        $fromBalance = (float)$fromWallet['balance'];
        if ($fromBalance < $amount) throw new InvalidArgumentException("Insufficient balance");

        // to wallet create if missing
        $toWallet = $this->walletsDao->getByUserAndCurrency($user_id, $to_currency_id);
        if (!$toWallet) {
            $newWalletId = $this->walletsDao->createWallet($user_id, $to_currency_id, 0);
            $toWallet = $this->walletsDao->get_by_id($newWalletId);
        }
        if (!$toWallet) throw new InvalidArgumentException("To wallet not found / could not be created");

        $toBalance = (float)$toWallet['balance'];
        $receive = $amount * $rate;

        $conn = Database::connect();
        $conn->beginTransaction();

        try {
            // update balances
            $this->walletsDao->updateBalance((int)$fromWallet['id'], $fromBalance - $amount);
            $this->walletsDao->updateBalance((int)$toWallet['id'],   $toBalance + $receive);

            // create FILLED order
            $this->ordersDao->insert([
                'user_id' => $user_id,
                'base_currency_id' => $from_currency_id,
                'quote_currency_id' => $to_currency_id,
                'side' => 'SELL',
                'price' => $rate,
                'amount' => $amount,
                'status' => 'FILLED'
            ]);

            // transactions (withdraw from base, deposit to quote)
            $this->txDao->insert([
                'wallet_id' => (int)$fromWallet['id'],
                'type' => 'WITHDRAWAL',
                'amount' => $amount
            ]);

            $this->txDao->insert([
                'wallet_id' => (int)$toWallet['id'],
                'type' => 'DEPOSIT',
                'amount' => $receive
            ]);

            $conn->commit();

            return [
                'success' => true,
                'from_wallet_id' => (int)$fromWallet['id'],
                'to_wallet_id' => (int)$toWallet['id'],
                'spent' => $amount,
                'rate' => $rate,
                'received' => $receive
            ];
        } catch (Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
