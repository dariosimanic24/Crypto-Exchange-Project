<?php

require_once __DIR__ . '/../dao/UsersDao.php';

class JwtAuthService
{
    private UsersDao $usersDao;

    // JWT config – SAMO ZA AUTH, nema veze sa bazom
    private const JWT_SECRET       = 'change_this_super_secret_jwt_key_1234567890';
    private const JWT_ISSUER       = 'crypto-exchange-app';
    private const JWT_TTL_SECONDS  = 86400; // 24h

    public function __construct()
    {
        $this->usersDao = new UsersDao();
    }

    /**
     * Login user + generate JWT token
     *
     * @throws InvalidArgumentException
     */
    public function login(string $email, string $password): array
    {
        $email    = trim($email);
        $password = trim($password);

        if ($email === '' || $password === '') {
            throw new InvalidArgumentException('Email and password are required');
        }

        $user = $this->usersDao->getByEmail($email);
        if (!$user) {
            throw new InvalidArgumentException('Invalid email or password');
        }

        if (!password_verify($password, $user['password'])) {
            throw new InvalidArgumentException('Invalid email or password');
        }

        // napravi JWT token
        $token = $this->generateToken($user);

        // nikad ne vraćamo password
        unset($user['password']);

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }

    /**
     * Generate JWT (HS256)
     */
    public function generateToken(array $user): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $now = time();

        $payload = [
            'sub'      => $user['id'],
            'email'    => $user['email'],
            'is_admin' => (int)($user['is_admin'] ?? 0),
            'iat'      => $now,
            'exp'      => $now + self::JWT_TTL_SECONDS,
            'iss'      => self::JWT_ISSUER,
        ];

        $base64Header  = $this->base64UrlEncode(json_encode($header));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $base64Header . '.' . $base64Payload,
            self::JWT_SECRET,
            true
        );

        $base64Signature = $this->base64UrlEncode($signature);

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    /**
     * Verify token and return payload (or null if invalid/expired)
     */
    public function verifyToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $signatureCheck = hash_hmac(
            'sha256',
            $headerB64 . '.' . $payloadB64,
            self::JWT_SECRET,
            true
        );

        $signatureCheckB64 = $this->base64UrlEncode($signatureCheck);

        if (!hash_equals($signatureCheckB64, $signatureB64)) {
            return null;
        }

        $payloadJson = $this->base64UrlDecode($payloadB64);
        $payload     = json_decode($payloadJson, true);

        if (!is_array($payload)) {
            return null;
        }

        // provjera exp
        if (isset($payload['exp']) && time() > (int)$payload['exp']) {
            return null;
        }

        return $payload;
    }

    /**
     * Get user data from token (returns sanitized user or null)
     */
    public function getUserFromToken(string $token): ?array
    {
        $payload = $this->verifyToken($token);
        if ($payload === null) {
            return null;
        }

        $userId = $payload['sub'] ?? null;
        if (!$userId) {
            return null;
        }

        $user = $this->usersDao->getById($userId);
        if (!$user) {
            return null;
        }

        unset($user['password']);

        return $user;
    }

    /* ********************
     * Helpers
     * ********************/

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data  .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
