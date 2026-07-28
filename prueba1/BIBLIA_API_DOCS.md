# Integración con A Bíblia Digital API (GraphQL)

Esta integración permite consumir los datos bíblicos de A Bíblia Digital a través de una API GraphQL.

## Endpoints Disponibles

### 1. Obtener todos los libros bíblicos
```
GET /api/biblia/books
```

**Respuesta:**
```json
{
  "data": {
    "books": [
      {
        "id": "1",
        "name": "Génesis",
        "author": "Moisés",
        "abbreviation": "Gn"
      },
      ...
    ]
  }
}
```

### 2. Obtener un libro específico con capítulos
```
GET /api/biblia/book/{bookId}
```

**Parámetros:**
- `bookId`: ID del libro (ej: "1" para Génesis)

**Respuesta:**
```json
{
  "data": {
    "book": {
      "id": "1",
      "name": "Génesis",
      "author": "Moisés",
      "abbreviation": "Gn",
      "chapters": [
        {
          "number": 1,
          "verses": [
            {
              "number": 1,
              "text": "En el principio creó Dios los cielos y la tierra."
            },
            ...
          ]
        }
      ]
    }
  }
}
```

### 3. Obtener un versículo específico
```
GET /api/biblia/verse/{book}/{chapter}/{verse}
```

**Parámetros:**
- `book`: Nombre del libro (ej: "Génesis")
- `chapter`: Número del capítulo
- `verse`: Número del versículo

**Ejemplo:**
```
GET /api/biblia/verse/Génesis/1/1
```

**Respuesta:**
```json
{
  "data": {
    "verse": {
      "book": {
        "name": "Génesis",
        "abbreviation": "Gn"
      },
      "chapter": 1,
      "number": 1,
      "text": "En el principio creó Dios los cielos y la tierra."
    }
  }
}
```

### 4. Obtener un capítulo completo
```
GET /api/biblia/chapter/{book}/{chapter}
```

**Parámetros:**
- `book`: Nombre del libro
- `chapter`: Número del capítulo

**Ejemplo:**
```
GET /api/biblia/chapter/Génesis/1
```

**Respuesta:**
```json
{
  "data": {
    "chapter": {
      "book": {
        "name": "Génesis",
        "abbreviation": "Gn"
      },
      "number": 1,
      "verses": [
        {
          "number": 1,
          "text": "En el principio creó Dios los cielos y la tierra."
        },
        ...
      ]
    }
  }
}
```

### 5. Buscar versículos por palabra clave
```
GET /api/biblia/search?q=palabra
```

**Parámetros Query:**
- `q`: Palabra o frase a buscar (requerido, min: 2 caracteres, máx: 100)

**Ejemplo:**
```
GET /api/biblia/search?q=amor
```

**Respuesta:**
```json
{
  "data": {
    "search": [
      {
        "book": {
          "name": "1 Juan",
          "abbreviation": "1Jo"
        },
        "chapter": 4,
        "verse": 8,
        "text": "El que no ama no conoce a Dios, pues Dios es amor."
      },
      ...
    ]
  }
}
```

### 6. Limpiar caché
```
POST /api/biblia/cache/clear
```

**Respuesta:**
```json
{
  "message": "Caché limpiado correctamente"
}
```

## Uso en el Código

### Inyectar el servicio en un controlador

```php
<?php

namespace App\Http\Controllers;

use App\Services\BibliaDigitalService;

class MiControlador extends Controller
{
    public function __construct(protected BibliaDigitalService $bibliaService)
    {
    }

    public function miMetodo()
    {
        // Obtener todos los libros
        $books = $this->bibliaService->getBooks();
        
        // Obtener un versículo
        $verse = $this->bibliaService->getVerse('Génesis', 1, 1);
        
        // Buscar versículos
        $resultados = $this->bibliaService->searchVerses('amor');
        
        return response()->json($books);
    }
}
```

### Uso desde JavaScript/Frontend

```javascript
// Obtener todos los libros
fetch('/api/biblia/books')
  .then(response => response.json())
  .then(data => console.log(data));

// Obtener un versículo específico
fetch('/api/biblia/verse/Génesis/1/1')
  .then(response => response.json())
  .then(data => console.log(data));

// Buscar versículos
fetch('/api/biblia/search?q=amor')
  .then(response => response.json())
  .then(data => console.log(data));
```

## Características

- ✅ Caché inteligente (24 horas)
- ✅ Manejo de errores
- ✅ Soporte para GraphQL nativo
- ✅ Búsqueda por palabras clave
- ✅ Acceso a capítulos completos
- ✅ Acceso a versículos individuales

## Notas

- Los resultados se cachean por 24 horas para mejorar el rendimiento
- La API de A Bíblia Digital es pública y no requiere autenticación
- Se utiliza Guzzle para las peticiones HTTP
- Los errores se retornan en formato JSON

## Configuración

No se requiere configuración adicional. El servicio usa la URL pública de la API:
```
https://api.abiblia.org/graphql
```
