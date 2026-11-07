<?php

namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse as BaseTestResponse;

/**
 * TestResponse personalizado que fuerza encoding UTF-8 para evitar
 * el bug de PHPUnit donde mb_detect_encoding falla en respuestas HTML grandes
 * detectando UTF-16 en lugar de UTF-8.
 */
class FixedTestResponse extends BaseTestResponse
{
    /**
     * Obtiene el contenido de la respuesta forzando encoding UTF-8.
     *
     * Workaround para bug de PHPUnit 12.x: mb_detect_encoding detecta
     * incorrectamente UTF-16 (BOM 0xFFFE) en respuestas HTML grandes.
     */
    public function getContent(): string
    {
        $content = parent::getContent();

        // Detectar y corregir UTF-16 mal detectado (BOM 0xFFFE o 0xFEFF)
        if (strlen($content) >= 2) {
            $bom = substr($content, 0, 2);
            if ($bom === "\xFF\xFE" || $bom === "\xFE\xFF") {
                return mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
            } elseif (strpos($content, "\x00") !== false) {
                // Heurística: muchos bytes nulos sugieren UTF-16
                $nullRatio = substr_count($content, "\x00") / strlen($content);
                if ($nullRatio > 0.1) {
                    return mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
                }
            }
        }

        return $content;
    }
}