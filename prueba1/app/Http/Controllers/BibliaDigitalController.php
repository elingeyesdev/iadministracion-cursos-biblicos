<?php

namespace App\Http\Controllers;

use App\Services\BibliaDigitalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BibliaDigitalController extends Controller
{
    public function __construct(protected BibliaDigitalService $bibliaService)
    {
    }

    /**
     * Muestra la vista del buscador de la Biblia.
     */
    public function index()
    {
        return view('biblia.index');
    }

    /**
     * Endpoint web para buscar referencias o palabras claves.
     */
    public function buscar(Request $request): JsonResponse
    {
        $q = $request->query('q');
        if (!$q) {
            return response()->json(['error' => 'Debe ingresar una referencia o palabra.'], 400);
        }

        // 1. Intentar buscar como referencia usando bible-api.com (soporta Valera en español)
        try {
            $response = Http::timeout(5)->get("https://bible-api.com/" . urlencode($q) . "?translation=valera");
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // Continuar al fallback si falla la API externa
        }

        // 2. Si falla o no es una referencia, buscar por palabra clave usando BibliaDigitalService
        try {
            $results = $this->bibliaService->searchVerses($q);
            if (isset($results['data']['search']) && !empty($results['data']['search'])) {
                $searchList = $results['data']['search'];
                
                // Formatear la respuesta para que coincida con los campos que espera el JS de la vista
                $formattedVerses = [];
                foreach ($searchList as $index => $item) {
                  $formattedVerses[] = [
                      'verse' => $index + 1,
                      'text' => "[" . $item['book']['name'] . " " . $item['chapter'] . ":" . $item['verse'] . "] " . $item['text'] . "\n"
                  ];
                }

                return response()->json([
                    'reference' => 'Resultados para la palabra: "' . $q . '"',
                    'translation_name' => 'A Bíblia Digital',
                    'verses' => $formattedVerses,
                    'text' => ''
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al buscar en el servicio de Biblia: ' . $e->getMessage()], 500);
        }

        return response()->json(['error' => 'No se encontraron resultados para la referencia o palabra clave dada.'], 404);
    }

    /**
     * API: Obtener todos los libros
     */
    public function getBooks(): JsonResponse
    {
        return response()->json($this->bibliaService->getBooks());
    }

    /**
     * API: Obtener un libro con capítulos
     */
    public function getBook(string $bookId): JsonResponse
    {
        return response()->json($this->bibliaService->getBookWithChapters($bookId));
    }

    /**
     * API: Obtener un versículo
     */
    public function getVerse(string $book, int $chapter, int $verse): JsonResponse
    {
        return response()->json($this->bibliaService->getVerse($book, $chapter, $verse));
    }

    /**
     * API: Obtener un capítulo
     */
    public function getChapter(string $book, int $chapter): JsonResponse
    {
        return response()->json($this->bibliaService->getChapter($book, $chapter));
    }

    /**
     * API: Buscar por palabra clave
     */
    public function apiSearch(Request $request): JsonResponse
    {
        $q = $request->query('q');
        if (!$q) {
            return response()->json(['error' => 'Falta parámetro de consulta q'], 400);
        }
        return response()->json($this->bibliaService->searchVerses($q));
    }

    /**
     * API: Limpiar caché
     */
    public function clearCache(): JsonResponse
    {
        $this->bibliaService->clearCache();
        return response()->json(['message' => 'Caché limpiado correctamente']);
    }
}
