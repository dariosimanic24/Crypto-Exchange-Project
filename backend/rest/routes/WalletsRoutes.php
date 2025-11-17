<?php
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/wallets",
 *     tags={"wallets"},
 *     summary="Get all wallets",
 *     @OA\Response(
 *         response=200,
 *         description="Returns list of wallets"
 *     )
 * )
 */
Flight::route('GET /wallets', function() {
    try {
        $data = Flight::walletsService()->get_all();
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/wallets/{id}",
 *     tags={"wallets"},
 *     summary="Get wallet by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Wallet object"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Wallet not found"
 *     )
 * )
 */
Flight::route('GET /wallets/@id', function($id) {
    try {
        $data = Flight::walletsService()->get_by_id($id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/wallets",
 *     tags={"wallets"},
 *     summary="Create a new wallet",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id","currency_id"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="currency_id", type="integer", example=1),
 *             @OA\Property(property="balance", type="number", format="float", example=0.0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Wallet created"
 *     )
 * )
 */
Flight::route('POST /wallets', function() {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::walletsService()->add($payload);
        Flight::json($data, 201);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/wallets/{id}",
 *     tags={"wallets"},
 *     summary="Update an existing wallet",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="balance", type="number", format="float", example=120.5)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Wallet updated"
 *     )
 * )
 */
Flight::route('PUT /wallets/@id', function($id) {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::walletsService()->update($payload, $id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/wallets/{id}",
 *     tags={"wallets"},
 *     summary="Delete a wallet",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Wallet deleted"
 *     )
 * )
 */
Flight::route('DELETE /wallets/@id', function($id) {
    try {
        $ok = Flight::walletsService()->delete($id);
        Flight::json(['success' => $ok]);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});
