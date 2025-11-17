<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Crypto Exchange API",
 *     version="1.0.0",
 *     description="Backend API for Web Programming project - Crypto Exchange"
 * )
 *
 * @OA\Server(
 *     url="http://localhost/Crypto-Exchange-Project-main/backend",
 *     description="Local development server"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id","name","email"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-16 22:25:27")
 * )
 *
 * @OA\Schema(
 *     schema="Currency",
 *     type="object",
 *     required={"id","code","name","decimals"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="BTC"),
 *     @OA\Property(property="name", type="string", example="Bitcoin"),
 *     @OA\Property(property="decimals", type="integer", example=8)
 * )
 *
 * @OA\Schema(
 *     schema="Wallet",
 *     type="object",
 *     required={"id","user_id","currency_id","balance"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="currency_id", type="integer", example=1),
 *     @OA\Property(property="balance", type="number", format="double", example=0.12345678)
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     required={"id","user_id","base_currency_id","quote_currency_id","side","price","amount","status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="base_currency_id", type="integer", example=1),
 *     @OA\Property(property="quote_currency_id", type="integer", example=3),
 *     @OA\Property(property="side", type="string", enum={"BUY","SELL"}, example="BUY"),
 *     @OA\Property(property="price", type="number", format="double", example=65000.12345678),
 *     @OA\Property(property="amount", type="number", format="double", example=0.005),
 *     @OA\Property(property="status", type="string", enum={"OPEN","FILLED","CANCELLED"}, example="OPEN"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     required={"id","wallet_id","type","amount"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="wallet_id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", enum={"DEPOSIT","WITHDRAW","FILL_BUY","FILL_SELL"}, example="DEPOSIT"),
 *     @OA\Property(property="amount", type="number", format="double", example=100.5),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
