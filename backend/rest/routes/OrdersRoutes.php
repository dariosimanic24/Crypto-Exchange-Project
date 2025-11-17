<?php
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/orders",
 *     tags={"orders"},
 *     summary="Get all orders",
 *     @OA\Response(
 *         response=200,
 *         description="Returns list of orders"
 *     )
 * )
 */
Flight::route('GET /orders', function() {
    try {
        $data = Flight::ordersService()->get_all();
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/orders/{id}",
 *     tags={"orders"},
 *     summary="Get order by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order object"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order not found"
 *     )
 * )
 */
Flight::route('GET /orders/@id', function($id) {
    try {
        $data = Flight::ordersService()->get_by_id($id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/orders",
 *     tags={"orders"},
 *     summary="Create a new order",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id","base_currency_id","quote_currency_id","side","price","amount"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="base_currency_id", type="integer", example=1),
 *             @OA\Property(property="quote_currency_id", type="integer", example=3),
 *             @OA\Property(property="side", type="string", example="BUY"),
 *             @OA\Property(property="price", type="number", format="float", example=50000.0),
 *             @OA\Property(property="amount", type="number", format="float", example=0.01)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Order created"
 *     )
 * )
 */
Flight::route('POST /orders', function() {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::ordersService()->add($payload);
        Flight::json($data, 201);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/orders/{id}",
 *     tags={"orders"},
 *     summary="Update an existing order",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="CANCELLED")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order updated"
 *     )
 * )
 */
Flight::route('PUT /orders/@id', function($id) {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::ordersService()->update($payload, $id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/orders/{id}",
 *     tags={"orders"},
 *     summary="Delete an order",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order deleted"
 *     )
 * )
 */
Flight::route('DELETE /orders/@id', function($id) {
    try {
        $ok = Flight::ordersService()->delete($id);
        Flight::json(['success' => $ok]);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});
