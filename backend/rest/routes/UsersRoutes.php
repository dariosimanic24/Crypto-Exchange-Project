<?php

use OpenApi\Annotations as OA;

Flight::route('GET /test-users', function() {
    echo "Users route works!";
});

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"users"},
 *     summary="Get all users (admin only)",
 *     @OA\Response(
 *         response=200,
 *         description="Returns list of users"
 *     )
 * )
 */
Flight::route('GET /users', function() {
    // samo admin smije gledati sve usere
    Flight::auth_middleware()->requireAdmin();

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
 *     summary="Get user by ID (admin only)",
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
    Flight::auth_middleware()->requireAdmin();

    try {
        $data = Flight::usersService()->get_by_id($id);
        if (!$data) {
            Flight::json(['error' => 'User not found'], 404);
            return;
        }
        Flight::json($data);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});


/**
 * @OA\Post(
 *     path="/users/register",
 *     tags={"users"},
 *     summary="Register a new user (guest only)",
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
 *         description="User registered"
 *     )
 * )
 */
Flight::route('POST /users/register', function() {
    // samo guest smije registraciju
    Flight::auth_middleware()->requireGuest();

    // prvo pokušaj JSON (RequestValidationMiddleware), fallback na form-data
    $payload = Flight::get('jsonBody');
    if ($payload === null) {
        $payload = Flight::request()->data->getData();
    }

    try {
        $data = Flight::usersService()->add($payload); // add već hashira password + uklanja ga iz outputa
        Flight::json($data, 201);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});


/**
 * @OA\Post(
 *     path="/users",
 *     tags={"users"},
 *     summary="Create a new user (admin only)",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","password"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="password", type="string", example="secret123"),
 *             @OA\Property(property="is_admin", type="integer", example=0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created by admin"
 *     )
 * )
 */
Flight::route('POST /users', function() {
    // admin-only CRUD
    Flight::auth_middleware()->requireAdmin();

    $payload = Flight::get('jsonBody');
    if ($payload === null) {
        $payload = Flight::request()->data->getData();
    }

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
 *     summary="Update an existing user (admin only)",
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
 *             @OA\Property(property="email", type="string", example="updated@example.com"),
 *             @OA\Property(property="is_admin", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User updated"
 *     )
 * )
 */
Flight::route('PUT /users/@id', function($id) {
    Flight::auth_middleware()->requireAdmin();

    $payload = Flight::get('jsonBody');
    if ($payload === null) {
        $payload = Flight::request()->data->getData();
    }

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
 *     summary="Delete a user (admin only)",
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
    Flight::auth_middleware()->requireAdmin();

    try {
        $ok = Flight::usersService()->delete($id);
        Flight::json(['success' => $ok]);
    } catch (Throwable $e) {
        Flight::halt(400, $e->getMessage());
    }
});
