<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Tests\FixedTestResponse;

abstract class TestCase extends BaseTestCase
{
    use HasFixedAssertions;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Forzar encoding UTF-8 interno para PHPUnit (workaround bug encoding detection)
        mb_internal_encoding('UTF-8');
    }

    /**
     * Crea una respuesta de prueba personalizada que fuerza encoding UTF-8.
     *
     * Workaround para bug de PHPUnit 12.x: mb_detect_encoding detecta
     * incorrectamente UTF-16 en respuestas HTML grandes.
     */
    protected function createTestResponse($response, $request): FixedTestResponse
    {
        return tap(FixedTestResponse::fromBaseResponse($response, $request), function ($resp) {
            $resp->withExceptions(
                $this->app->bound(\Illuminate\Testing\LoggedExceptionCollection::class)
                    ? $this->app->make(\Illuminate\Testing\LoggedExceptionCollection::class)
                    : new \Illuminate\Testing\LoggedExceptionCollection
            );
        });
    }
}
