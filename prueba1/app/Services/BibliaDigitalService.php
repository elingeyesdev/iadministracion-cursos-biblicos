<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BibliaDigitalService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $graphqlUrl;
    protected bool $useMockData;
    protected bool $debug;
    protected int $cacheExpiration;

    public function __construct()
    {
        $this->baseUrl = config('biblia.base_url', 'https://www.seudominio.com.br/api');
        $this->graphqlUrl = config('biblia.graphql_url', 'https://www.seudominio.com.br/graphql');
        $this->useMockData = config('biblia.use_mock_data', false);
        $this->debug = config('biblia.debug', false);
        $this->cacheExpiration = config('biblia.cache_expiration', 86400);

        $this->client = new Client([
            'timeout' => config('biblia.timeout', 30),
            'connect_timeout' => config('biblia.connect_timeout', 10),
            'verify' => false, // Para desarrollo local
        ]);
    }

    /**
     * Ejecutar una consulta GraphQL
     */
    public function query(string $query, array $variables = []): array
    {
        if ($this->useMockData) {
            if ($this->debug) {
                Log::info('Using mock data for GraphQL query');
            }
            return $this->getMockResponse($query, $variables);
        }

        try {
            if ($this->debug) {
                Log::info('Executing GraphQL query', ['url' => $this->graphqlUrl]);
            }

            $response = $this->client->post($this->graphqlUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'query' => $query,
                    'variables' => $variables,
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            
            if (isset($body['errors'])) {
                Log::warning('GraphQL errors', $body['errors']);
                return ['errors' => $body['errors']];
            }

            return $body;
        } catch (GuzzleException $e) {
            Log::error('BibliaDigitalService Guzzle Error: ' . $e->getMessage());
            return [
                'errors' => [
                    ['message' => 'Error al conectar con la API: ' . $e->getMessage()]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('BibliaDigitalService Exception: ' . $e->getMessage());
            return [
                'errors' => [
                    ['message' => $e->getMessage()]
                ]
            ];
        }
    }

    /**
     * Obtener respuesta mock para datos de prueba
     */
    protected function getMockResponse(string $query, array $variables = []): array
    {
        $books = [
            [
                'id' => '1',
                'name' => 'Génesis',
                'author' => 'Moisés',
                'abbreviation' => 'Gn'
            ],
            [
                'id' => '2',
                'name' => 'Éxodo',
                'author' => 'Moisés',
                'abbreviation' => 'Ex'
            ],
            [
                'id' => '19',
                'name' => 'Salmos',
                'author' => 'Varios',
                'abbreviation' => 'Sal'
            ],
        ];

        if (stripos($query, 'query GetBook') !== false) {
            $bookId = (string) ($variables['id'] ?? '1');
            $book = collect($books)->firstWhere('id', $bookId) ?? $books[0];

            return [
                'data' => [
                    'book' => [
                        'id' => $book['id'],
                        'name' => $book['name'],
                        'author' => $book['author'],
                        'abbreviation' => $book['abbreviation'],
                        'chapters' => array_map(fn($number) => [
                            'number' => $number,
                            'verses' => [
                                ['number' => 1, 'text' => "{$book['name']} {$number}:1 - Texto de ejemplo."],
                                ['number' => 2, 'text' => "{$book['name']} {$number}:2 - Texto de ejemplo."],
                                ['number' => 3, 'text' => "{$book['name']} {$number}:3 - Texto de ejemplo."],
                            ],
                        ], range(1, 5)),
                    ],
                ],
            ];
        }

        if (stripos($query, 'query GetChapter') !== false) {
            $bookName = $variables['book'] ?? 'Génesis';
            $chapter = (int) ($variables['chapter'] ?? 1);

            return [
                'data' => [
                    'chapter' => [
                        'book' => ['name' => $bookName, 'abbreviation' => substr($bookName, 0, 3)],
                        'number' => $chapter,
                        'verses' => array_map(fn($number) => [
                            'number' => $number,
                            'text' => "{$bookName} {$chapter}:{$number} - Texto de ejemplo.",
                        ], range(1, 5)),
                    ],
                ],
            ];
        }

        if (stripos($query, 'query SearchVerse') !== false) {
            return [
                'data' => [
                    'verse' => [
                        'book' => ['name' => 'Génesis', 'abbreviation' => 'Gn'],
                        'chapter' => $variables['chapter'] ?? 1,
                        'number' => $variables['verse'] ?? 1,
                        'text' => 'En el principio creó Dios los cielos y la tierra.',
                    ],
                ],
            ];
        }

        if (stripos($query, 'query SearchVerses') !== false) {
            return [
                'data' => [
                    'search' => [
                        [
                            'book' => ['name' => '1 Juan', 'abbreviation' => '1Jo'],
                            'chapter' => 4,
                            'verse' => 8,
                            'text' => 'El que no ama no conoce a Dios, pues Dios es amor.',
                        ],
                        [
                            'book' => ['name' => '1 Corintios', 'abbreviation' => '1Co'],
                            'chapter' => 13,
                            'verse' => 4,
                            'text' => 'El amor es paciente, es benigno; el amor no tiene envidia, el amor no es jactancioso, no se envanece.',
                        ],
                    ],
                ],
            ];
        }

        return [
            'data' => [
                'books' => $books,
            ],
        ];
    }

    /**
     * Obtener todos los libros bíblicos
     */
    public function getBooks(): array
    {
        $query = <<<'GRAPHQL'
            query {
                books {
                    id
                    name
                    author
                    abbreviation
                }
            }
        GRAPHQL;

        return Cache::remember('biblia_books', $this->cacheExpiration, function () use ($query) {
            return $this->query($query);
        });
    }

    /**
     * Obtener un libro específico con sus capítulos
     */
    public function getBookWithChapters(string $bookId): array
    {
        $query = <<<'GRAPHQL'
            query GetBook($id: String!) {
                book(id: $id) {
                    id
                    name
                    author
                    abbreviation
                    chapters {
                        number
                        verses {
                            number
                            text
                        }
                    }
                }
            }
        GRAPHQL;

        return Cache::remember("biblia_book_{$bookId}", $this->cacheExpiration, function () use ($query, $bookId) {
            return $this->query($query, ['id' => $bookId]);
        });
    }

    /**
     * Obtener un versículo específico
     */
    public function getVerse(string $book, int $chapter, int $verse): array
    {
        $query = <<<'GRAPHQL'
            query SearchVerse($book: String!, $chapter: Int!, $verse: Int!) {
                verse(book: $book, chapter: $chapter, verse: $verse) {
                    book {
                        name
                        abbreviation
                    }
                    chapter
                    number
                    text
                }
            }
        GRAPHQL;

        return $this->query($query, [
            'book' => $book,
            'chapter' => $chapter,
            'verse' => $verse,
        ]);
    }

    /**
     * Buscar versículos por palabra clave
     */
    public function searchVerses(string $query): array
    {
        $graphqlQuery = <<<'GRAPHQL'
            query SearchVerses($search: String!) {
                search(search: $search) {
                    book {
                        name
                        abbreviation
                    }
                    chapter
                    verse
                    text
                }
            }
        GRAPHQL;

        return $this->query($graphqlQuery, ['search' => $query]);
    }

    /**
     * Obtener un capítulo completo
     */
    public function getChapter(string $book, int $chapter): array
    {
        $query = <<<'GRAPHQL'
            query GetChapter($book: String!, $chapter: Int!) {
                chapter(book: $book, chapter: $chapter) {
                    book {
                        name
                        abbreviation
                    }
                    number
                    verses {
                        number
                        text
                    }
                }
            }
        GRAPHQL;

        return Cache::remember("biblia_chapter_{$book}_{$chapter}", $this->cacheExpiration, function () use ($query, $book, $chapter) {
            return $this->query($query, [
                'book' => $book,
                'chapter' => $chapter,
            ]);
        });
    }

    /**
     * Limpiar caché
     */
    public function clearCache(): void
    {
        Cache::forget('biblia_books');
        Cache::tags(['biblia'])->flush();
    }
}
