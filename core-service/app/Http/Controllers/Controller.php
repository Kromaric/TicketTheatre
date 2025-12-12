<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="TicketTheatre API",
 *     description="API de gestion de réservations de spectacles",
 *     @OA\Contact(
 *         email="support@tickettheatre.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8082/api",
 *     description="Core Service - Development"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token d'authentification Sanctum"
 * )
 *
 * @OA\Tag(
 *     name="Categories",
 *     description="Gestion des catégories de spectacles"
 * )
 *
 * @OA\Tag(
 *     name="Halls",
 *     description="Gestion des salles"
 * )
 *
 * @OA\Tag(
 *     name="Spectacles",
 *     description="Gestion des spectacles"
 * )
 *
 * @OA\Tag(
 *     name="Seances",
 *     description="Gestion des séances"
 * )
 *
 * @OA\Tag(
 *     name="Reservations",
 *     description="Gestion des réservations"
 * )
 */
class Controller
{
    //
}
