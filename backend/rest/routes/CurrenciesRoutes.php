<?php
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/currencies",
 *     tags={"currencies"},
 *     summary="Get all currencies",
 *     @OA\Response(
 *         response=200,
 *         description="Returns list of currencies"
 *     )
 * )
 */
Flight::route('GET /currencies', function() {
    try {
        $data = Flight::currenciesService()->get_all();
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/currencies/{id}",
 *     tags={"currencies"},
 *     summary="Get currency by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Currency object"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Currency not found"
 *     )
 * )
 */
Flight::route('GET /currencies/@id', function($id) {
    try {
        $data = Flight::currenciesService()->get_by_id($id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/currencies",
 *     tags={"currencies"},
 *     summary="Create a new currency",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"code","name","decimals"},
 *             @OA\Property(property="code", type="string", example="BTC"),
 *             @OA\Property(property="name", type="string", example="Bitcoin"),
 *             @OA\Property(property="decimals", type="integer", example=8)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Currency created"
 *     )
 * )
 */
Flight::route('POST /currencies', function() {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::currenciesService()->add($payload);
        Flight::json($data, 201);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/currencies/{id}",
 *     tags={"currencies"},
 *     summary="Update an existing currency",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Name"),
 *             @OA\Property(property="decimals", type="integer", example=6)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Currency updated"
 *     )
 * )
 */
Flight::route('PUT /currencies/@id', function($id) {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::currenciesService()->update($payload, $id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/currencies/{id}",
 *     tags={"currencies"},
 *     summary="Delete a currency",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Currency deleted"
 *     )
 * )
 */
Flight::route('DELETE /currencies/@id', function($id) {
    try {
        $ok = Flight::currenciesService()->delete($id);
        Flight::json(['success' => $ok]);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});
