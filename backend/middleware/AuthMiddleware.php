<?php

require_once __DIR__ . '/../rest/services/JwtAuthService.php';

class AuthMiddleware
{
    private JwtAuthService $authService;

    public function __construct()
    {
        $this->authService = new JwtAuthService();
    }

    /**
     * Probaj izvući Authorization header iz različitih mjesta
     */
    private function getAuthorizationHeader(): ?string
    {
        // 1) Najčešći slučaj
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        // 2) Neki Apache setupi koriste samo 'Authorization'
        if (!empty($_SERVER['Authorization'])) {
            return $_SERVER['Authorization'];
        }

        // 3) Fallback preko apache_request_headers / getallheaders
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();

            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
            if (isset($headers['authorization'])) {
                return $headers['authorization'];
            }
        } elseif (function_exists('getallheaders')) {
            $headers = getallheaders();

            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
            if (isset($headers['authorization'])) {
                return $headers['authorization'];
            }
        }

        return null;
    }

    /**
     * Uzmi Bearer token iz Authorization headera
     */
    private function getTokenFromHeader(): ?string
    {
        $header = $this->getAuthorizationHeader();

        if ($header && stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }

        return null;
    }

    /**
     * Ovo zovemo na rutama koje traže bilo kakav login
     * - ako nije logovan → 401
     * - ako je token nevažeći/istekao → 401
     */
    public function requireAuth(): void
    {
        $token = $this->getTokenFromHeader();

        if (!$token) {
            Flight::halt(401, json_encode(['error' => 'Authentication required']));
        }

        $user = $this->authService->getUserFromToken($token);

        if ($user === null) {
            Flight::halt(401, json_encode(['error' => 'Invalid or expired token']));
        }

        // Zakači usera na Flight context da ga rute mogu koristiti
        Flight::set('user', $user);
    }

    /**
     * Samo admin (is_admin = 1)
     */
    public function requireAdmin(): void
    {
        $this->requireAuth();

        $user = Flight::get('user');

        if ((int)($user['is_admin'] ?? 0) !== 1) {
            Flight::halt(403, json_encode(['error' => 'Forbidden: admin only']));
        }
    }

    /**
     * Samo guest (nije logovan)
     * - koristimo za login i register
     * - ako je već logovan → 403
     */
    public function requireGuest(): void
    {
        $token = $this->getTokenFromHeader();

        if (!$token) {
            return; // nema tokena → guest → ok
        }

        $user = $this->authService->getUserFromToken($token);

        if ($user !== null) {
            Flight::halt(403, json_encode(['error' => 'Already logged in']));
        }
    }
}
