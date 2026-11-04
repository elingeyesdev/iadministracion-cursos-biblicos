<?php

/**
 * @\OpenApi\Annotations\Info(
 *     title="I Administracion Cursos Biblicos API",
 *     version="1.0.0",
 *     description="Documentación de la API"
 * )
 *
 * @\OpenApi\Annotations\Get(
 *     path="/api/miembros",
 *     summary="Listar miembros",
 *     @\OpenApi\Annotations\Response(response=200, description="OK")
 * )
 *
 * @\OpenApi\Annotations\Post(
 *     path="/api/miembros",
 *     summary="Crear miembro",
 *     @\OpenApi\Annotations\RequestBody(
 *         required=true,
 *         @\OpenApi\Annotations\MediaType(
 *             mediaType="application/json"
 *         )
 *     ),
 *     @\OpenApi\Annotations\Response(response=201, description="Created")
 * )
 *
 * @\OpenApi\Annotations\Get(
 *     path="/api/miembros/{id}",
 *     summary="Obtener miembro por id",
 *     @\OpenApi\Annotations\Parameter(name="id", in="path", required=true, @\OpenApi\Annotations\Schema(type="integer")),
 *     @\OpenApi\Annotations\Response(response=200, description="OK")
 * )
 */
class SwaggerPaths
{
}
