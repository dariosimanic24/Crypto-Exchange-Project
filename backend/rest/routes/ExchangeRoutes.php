<?php
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *   path="/exchange",
 *   tags={"exchange"},
 *   summary="Execute exchange for logged-in user",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       required={"from_currency_id","to_currency_id","amount","rate"},
 *       @OA\Property(property="from_currency_id", type="integer", example=1),
 *       @OA\Property(property="to_currency_id", type="integer", example=3),
 *       @OA\Property(property="amount", type="number", format="float", example=0.01),
 *       @OA\Property(property="rate", type="number", format="float", example=50000)
 *     )
 *   ),
 *   @OA\Response(response=200, description="Exchange executed")
 * )
 */
Flight::route('POST /exchange', function () {
    Flight::auth_middleware()->requireAuth();

    $payload = Flight::get('jsonBody') ?? Flight::request()->data->getData();

    try {
        // AuthMiddleware bi trebao postaviti user u context (kao sto /auth/me radi)
        $u = Flight::get('user');

        if (!$u || !isset($u['id'])) {
            Flight::halt(401, "Unauthorized (user not in context). Make sure AuthMiddleware sets Flight::set('user', ...).");
        }

        $result = Flight::exchangeService()->execute((int)$u['id'], (array)$payload);
        Flight::json($result);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});
