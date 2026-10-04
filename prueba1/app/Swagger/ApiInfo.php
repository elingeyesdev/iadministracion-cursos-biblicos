<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="I Administracion Cursos Biblicos API",
 *     version="1.0.0",
 *     description="Documentación de la API",
 *     @OA\Contact(email="soporte@ejemplo.com")
 * )
 */
class ApiInfo
{
}

// Archivo para que swagger-php detecte la anotación @OA\Info
