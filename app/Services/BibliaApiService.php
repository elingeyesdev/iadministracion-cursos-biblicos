<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Cliente de la API pública GetBible (https://api.getbible.net/v2),
 * que sí incluye traducciones en español (Reina-Valera 1909).
 */
class BibliaApiService
{
    protected string $baseUrl;
    protected string $translation;
    protected bool $verifySsl;

    /** Abreviaturas frecuentes en español => nombre del libro. */
    protected array $aliases = [
        'gn' => 'Génesis', 'gen' => 'Génesis',
        'ex' => 'Éxodo', 'exo' => 'Éxodo',
        'lv' => 'Levítico', 'lev' => 'Levítico',
        'nm' => 'Números', 'num' => 'Números',
        'dt' => 'Deuteronomio', 'deut' => 'Deuteronomio',
        'jos' => 'Josué', 'jue' => 'Jueces', 'rt' => 'Rut',
        '1 s' => '1 Samuel', '2 s' => '2 Samuel',
        '1 r' => '1 Reyes', '2 r' => '2 Reyes',
        '1 cr' => '1 Crónicas', '2 cr' => '2 Crónicas',
        'esd' => 'Esdras', 'neh' => 'Nehemías', 'est' => 'Ester',
        'job' => 'Job', 'sal' => 'Salmos', 'salmo' => 'Salmos',
        'pr' => 'Proverbios', 'prov' => 'Proverbios',
        'ec' => 'Eclesiastés', 'ecl' => 'Eclesiastés',
        'cnt' => 'Cantares', 'is' => 'Isaías', 'isa' => 'Isaías',
        'jer' => 'Jeremías', 'lam' => 'Lamentaciones', 'ez' => 'Ezequiel',
        'dan' => 'Daniel', 'os' => 'Oseas', 'jl' => 'Joel',
        'am' => 'Amós', 'abd' => 'Obadías', 'jon' => 'Jonás',
        'miq' => 'Miqueas', 'nah' => 'Nahúm', 'hab' => 'Habacuc',
        'sof' => 'Sofonías', 'hag' => 'Hageo', 'zac' => 'Zacarías',
        'mal' => 'Malaquías',
        'mt' => 'Mateo', 'mat' => 'Mateo', 'mr' => 'Marcos', 'mar' => 'Marcos',
        'lc' => 'Lucas', 'luc' => 'Lucas', 'jn' => 'Juan', 'jua' => 'Juan',
        'hch' => 'Hechos', 'hec' => 'Hechos', 'ro' => 'Romanos', 'rom' => 'Romanos',
        '1 co' => '1 Corintios', '2 co' => '2 Corintios',
        'ga' => 'Gálatas', 'gal' => 'Gálatas', 'ef' => 'Efesios',
        'fil' => 'Filipenses', 'col' => 'Colosenses',
        '1 ts' => '1 Tesalonicenses', '2 ts' => '2 Tesalonicenses',
        '1 ti' => '1 Timoteo', '2 ti' => '2 Timoteo',
        'tit' => 'Tito', 'flm' => 'Filemón', 'heb' => 'Hebreos',
        'stg' => 'Santiago', '1 p' => '1 Pedro', '2 p' => '2 Pedro',
        '1 jn' => '1 Juan', '2 jn' => '2 Juan', '3 jn' => '3 Juan',
        'jud' => 'Judas', 'ap' => 'Apocalipsis', 'apo' => 'Apocalipsis',
    ];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('biblia.api_url', 'https://api.getbible.net/v2'), '/');
        $this->translation = config('biblia.translation', 'valera');
        // En algunos entornos locales (Laragon/Windows) PHP no tiene CA bundle
        // configurado, por eso la verificación SSL es configurable.
        $this->verifySsl = (bool) config('biblia.verify_ssl', true);
    }

    public function translationName(): string
    {
        return config('biblia.translation_name', 'Reina Valera 1909');
    }

    /**
     * Lista de libros de la traducción: [número => nombre].
     *
     * @return array<int, string>
     */
    public function getBooks(): array
    {
        return Cache::remember("biblia_api_books_{$this->translation}", 86400, function () {
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->timeout(15)
                ->get("{$this->baseUrl}/{$this->translation}/books.json");

            if (! $response->successful()) {
                return [];
            }

            $books = [];
            foreach ((array) $response->json() as $id => $book) {
                if (isset($book['name'])) {
                    $books[(int) $id] = $book['name'];
                }
            }

            return $books;
        });
    }

    /**
     * Versículos de un capítulo: [número => texto].
     *
     * @return array<int, string>
     */
    public function getChapter(int $bookNumber, int $chapter): array
    {
        return Cache::remember("biblia_api_chapter_{$this->translation}_{$bookNumber}_{$chapter}", 86400, function () use ($bookNumber, $chapter) {
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->timeout(15)
                ->get("{$this->baseUrl}/{$this->translation}/{$bookNumber}/{$chapter}.json");

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            $verses = [];
            foreach ($data['verses'] ?? [] as $verse) {
                $verses[(int) $verse['verse']] = trim((string) ($verse['text'] ?? ''));
            }

            return $verses;
        });
    }

    /**
     * Interpreta una referencia en español: "Juan 3:16", "Salmos 23",
     * "1 Corintios 13:4-7", "Juan 3.16", etc.
     *
     * @return array{book:int, book_name:string, chapter:int, verse:?int, verse_end:?int, reference:string}|null
     */
    public function parseReference(string $query): ?array
    {
        $normalized = $this->normalize($query);

        if (! preg_match('/^((?:[1-3]\s+)?[a-z]+(?:\s+[a-z]+)*?)\s+(\d+)(?:\s*[:.,]\s*(\d+)(?:\s*[-–]\s*(\d+))?)?$/', $normalized, $matches)) {
            return null;
        }

        $bookPart = trim($matches[1]);
        $chapter = (int) $matches[2];
        $verse = isset($matches[3]) && $matches[3] !== '' ? (int) $matches[3] : null;
        $verseEnd = isset($matches[4]) && $matches[4] !== '' ? (int) $matches[4] : null;

        if ($chapter < 1) {
            return null;
        }

        $book = $this->resolveBook($bookPart);

        if (! $book) {
            return null;
        }

        $reference = $book['name'].' '.$chapter;

        if ($verse !== null) {
            $reference .= ':'.$verse;
            if ($verseEnd !== null && $verseEnd > $verse) {
                $reference .= '-'.$verseEnd;
            }
        }

        return [
            'book' => $book['number'],
            'book_name' => $book['name'],
            'chapter' => $chapter,
            'verse' => $verse,
            'verse_end' => $verseEnd,
            'reference' => $reference,
        ];
    }

    /**
     * Resuelve el nombre o abreviatura de un libro a su número.
     *
     * @return array{number:int, name:string}|null
     */
    protected function resolveBook(string $bookPart): ?array
    {
        $books = $this->getBooks();

        if (empty($books)) {
            return null;
        }

        $needle = $this->normalize($bookPart);

        // 1. Abreviatura conocida
        if (isset($this->aliases[$needle])) {
            $target = $this->normalize($this->aliases[$needle]);
            foreach ($books as $number => $name) {
                if ($this->normalize($name) === $target) {
                    return ['number' => $number, 'name' => $name];
                }
            }
        }

        // 2. Coincidencia exacta
        foreach ($books as $number => $name) {
            if ($this->normalize($name) === $needle) {
                return ['number' => $number, 'name' => $name];
            }
        }

        // 3. Coincidencia por prefijo (p. ej. "gen" -> Génesis, "mat" -> Mateo)
        $candidates = [];
        foreach ($books as $number => $name) {
            $normalizedName = $this->normalize($name);
            if (str_starts_with($normalizedName, $needle) || str_starts_with($needle, $normalizedName)) {
                $candidates[] = ['number' => $number, 'name' => $name, 'length' => strlen($normalizedName)];
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, fn ($a, $b) => $a['length'] <=> $b['length']);

        return $candidates[0];
    }

    protected function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ]);
        $value = str_replace(['.', ','], '', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }
}
