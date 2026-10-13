<?php

namespace App\Http\Controllers;

use App\Services\BibliaApiService;
use App\Services\BibliaDigitalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BibliaDigitalController extends Controller
{
    public function __construct(
        protected BibliaDigitalService $bibliaService,
        protected BibliaApiService $bibliaApi,
    ) {
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
        $q = trim((string) $request->query('q'));

        if ($q === '') {
            return response()->json(['error' => 'Debe ingresar una referencia o palabra.'], 400);
        }

        // 1. Interpretar como referencia bíblica en español (Juan 3:16, Salmos 23,
        //    1 Corintios 13:4-7...) y resolverla con GetBible (traducción valera).
        $reference = $this->bibliaApi->parseReference($q);

        if ($reference) {
            return $this->responderReferencia($reference);
        }

        // 2. Si no es una referencia reconocida, intentar búsqueda por palabra clave.
        try {
            $results = $this->bibliaService->searchVerses($q);

            if (isset($results['data']['search']) && !empty($results['data']['search'])) {
                $searchList = $results['data']['search'];

                // Formatear la respuesta para que coincida con los campos que espera el JS de la vista
                $formattedVerses = [];
                foreach ($searchList as $index => $item) {
                    $formattedVerses[] = [
                        'verse' => $index + 1,
                        'text' => '['.$item['book']['name'].' '.$item['chapter'].':'.$item['verse'].'] '.$item['text'].PHP_EOL,
                    ];
                }

                return response()->json([
                    'reference' => 'Resultados para la palabra: "'.$q.'"',
                    'translation_name' => 'A Bíblia Digital',
                    'verses' => $formattedVerses,
                    'text' => '',
                ]);
            }
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json([
            'error' => 'No se reconoció la referencia. Usa por ejemplo: Juan 3:16, Salmos 23 o 1 Corintios 13:4-7.',
        ], 404);
    }

    /**
     * Devuelve el capítulo (o versículo/rango) solicitado.
     *
     * @param  array{book:int, book_name:string, chapter:int, verse:?int, verse_end:?int, reference:string}  $reference
     */
    protected function responderReferencia(array $reference): JsonResponse
    {
        $verses = $this->bibliaApi->getChapter($reference['book'], $reference['chapter']);

        if (empty($verses)) {
            return response()->json([
                'error' => 'No se encontró el capítulo indicado (' . $reference['book_name'] . ' ' . $reference['chapter'] . ').',
            ], 404);
        }

        // Si se pidió un versículo o rango, recortar el capítulo.
        if ($reference['verse'] !== null) {
            $start = $reference['verse'];
            $end = $reference['verse_end'] ?? $start;

            $verses = array_filter(
                $verses,
                fn ($number) => $number >= $start && $number <= $end,
                ARRAY_FILTER_USE_KEY
            );
        }

        $formatted = [];
        foreach ($verses as $number => $text) {
            $formatted[] = ['verse' => $number, 'text' => $text];
        }

        $text = implode(PHP_EOL, array_map(
            fn ($verse) => $verse['verse'].'. '.$verse['text'],
            $formatted
        ));

        return response()->json([
            'reference' => $reference['reference'],
            'translation_name' => $this->bibliaApi->translationName(),
            'verses' => $formatted,
            'text' => $text,
        ]);
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
