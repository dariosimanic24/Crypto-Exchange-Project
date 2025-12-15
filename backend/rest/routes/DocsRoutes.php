<?php

use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * @OA\Info(
 *     title="Crypto Exchange API",
 *     version="1.0.0",
 *     description="OpenAPI documentation for Milestone 3"
 * )
 * @OA\Server(
 *     url="http://localhost/Crypto-Exchange-Project-main/backend",
 *     description="Local development server"
 * )
 */


/**
 * GET /docs/json
 */
Flight::route('GET /docs/json', function () {
    header('Content-Type: application/json');
    $openapi = Generator::scan([__DIR__]); 
    echo $openapi->toJson();
});

Flight::route('GET /docs', function () {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Crypto Exchange API Docs</title>
        <link rel="stylesheet"
              href="https://unpkg.com/swagger-ui-dist@4/swagger-ui.css" />
        <style>
            body {
                margin: 0;
                padding: 0;
            }
            #swagger-ui {
                width: 100%;
            }
        </style>
    </head>
    <body>
        <div id="swagger-ui"></div>

        <script src="https://unpkg.com/swagger-ui-dist@4/swagger-ui-bundle.js"></script>
        <script>
            window.onload = function() {
                SwaggerUIBundle({
                    url: "http://localhost/Crypto-Exchange-Project-main/backend/docs/json",
                    dom_id: "#swagger-ui"
                });
            };
        </script>
    </body>
    </html>
    <?php
});
