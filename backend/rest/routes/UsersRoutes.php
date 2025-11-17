<?php
use OpenApi\Annotations as OA;

Flight::route('GET /test-users', function() {
    echo "Users route works!";
});


/**
 * @OA\Get(
 *     path="/users",
 *     tags={"users"},
 *     summary="Get all users",
 *     @OA\Response(
 *         response=200,
 *         description="Returns list of users"
 *     )
 * )
 */
Flight::route('GET /users', function() {
    try {
        $data = Flight::usersService()->get_all();
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});


/**
 * @OA\Get(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Get user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User object"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     )
 * )
 */
Flight::route('GET /users/@id', function($id) {
    try {
        $data = Flight::usersService()->get_by_id($id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/users",
 *     tags={"users"},
 *     summary="Create a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","password"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="password", type="string", example="secret123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created"
 *     )
 * )
 */
Flight::route('POST /users', function() {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::usersService()->add($payload);
        Flight::json($data, 201);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Update an existing user",
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
 *             @OA\Property(property="email", type="string", example="updated@example.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User updated"
 *     )
 * )
 */
Flight::route('PUT /users/@id', function($id) {
    $payload = Flight::request()->data->getData();
    try {
        $data = Flight::usersService()->update($payload, $id);
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Delete a user",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User deleted"
 *     )
 * )
 */
Flight::route('DELETE /users/@id', function($id) {
    try {
        $ok = Flight::usersService()->delete($id);
        Flight::json(['success' => $ok]);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});
