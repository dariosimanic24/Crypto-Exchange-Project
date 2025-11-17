<?php
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/transactions",
 *     tags={"transactions"},
 *     summary="Get all transactions",
 *     @OA\Response(
 *         response=200,
 *         description="Returns list of transactions"
 *     )
 * )
 */
Flight::route('GET /transactions', function() {
    try {
        $data = Flight::transactionsService()->get_all();
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/transactions/{id}",
 *     tags={"transactions"},
 *     summary="Get transaction by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transaction object"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Transaction not found"
 *     )
 * )
 */
Flight::route('GET /transactions/@id', function($id) {
    try {
        $data = Flight::transactionsService()->get_by_id($id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/transactions",
 *     tags={"transactions"},
 *     summary="Create a new transaction",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"wallet_id","type","amount"},
 *             @OA\Property(property="wallet_id", type="integer", example=1),
 *             @OA\Property(property="type", type="string", example="DEPOSIT"),
 *             @OA\Property(property="amount", type="number", format="float", example=100.0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Transaction created"
 *     )
 * )
 */
Flight::route('POST /transactions', function() {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::transactionsService()->add($payload);
        Flight::json($data, 201);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/transactions/{id}",
 *     tags={"transactions"},
 *     summary="Update an existing transaction",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="amount", type="number", format="float", example=150.0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transaction updated"
 *     )
 * )
 */
Flight::route('PUT /transactions/@id', function($id) {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::transactionsService()->update($payload, $id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/transactions/{id}",
 *     tags={"transactions"},
 *     summary="Delete a transaction",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transaction deleted"
 *     )
 * )
 */
Flight::route('DELETE /transactions/@id', function($id) {
    try {
        $ok = Flight::transactionsService()->delete($id);
        Flight::json(['success' => $ok]);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});
