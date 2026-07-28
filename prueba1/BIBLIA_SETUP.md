# 📖 Integración A Bíblia Digital con GraphQL

Integración completa de la **API de A Bíblia Digital** en tu aplicación Laravel, utilizando GraphQL para consultar versículos bíblicos.

## 🚀 Características

- ✅ Consumo de API GraphQL de A Bíblia Digital
- ✅ Caché inteligente de 24 horas
- ✅ Búsqueda de versículos por palabra clave
- ✅ Acceso a capítulos completos
- ✅ Modelo `Versiculo` para almacenamiento local opcional
- ✅ Seeder para importar versículos
- ✅ Manejo de errores robusto
- ✅ Rutas API RESTful

## 📦 Instalación

### 1. Ya está instalado ✓

Guzzle HTTP viene preinstalado en Laravel por defecto.

### 2. Crear tablas (opcional)

Si deseas almacenar versículos localmente:

```bash
php artisan migrate
```

### 3. Importar versículos (opcional)

Para cargar versículos desde la API a tu base de datos:

```bash
php artisan db:seed --class=VersiculoSeeder
```

> ⚠️ Nota: Este proceso puede tomar varios minutos

## 🔌 Uso de la API

### Endpoints disponibles

#### 1️⃣ Obtener todos los libros
```bash
GET /api/biblia/books
```

#### 2️⃣ Obtener un libro específico
```bash
GET /api/biblia/book/1
```

#### 3️⃣ Obtener un versículo
```bash
GET /api/biblia/verse/Génesis/1/1
```

#### 4️⃣ Obtener un capítulo
```bash
GET /api/biblia/chapter/Génesis/1
```

#### 5️⃣ Buscar versículos
```bash
GET /api/biblia/search?q=amor
```

#### 6️⃣ Limpiar caché
```bash
POST /api/biblia/cache/clear
```

### Ejemplo con cURL

```bash
# Obtener el primer versículo de Génesis
curl http://localhost:8000/api/biblia/verse/Génesis/1/1

# Buscar versículos sobre "fe"
curl "http://localhost:8000/api/biblia/search?q=fe"
```

## 💻 Uso en el código

### En un controlador

```php
<?php

namespace App\Http\Controllers;

use App\Services\BibliaDigitalService;

class MiControlador extends Controller
{
    public function __construct(private BibliaDigitalService $biblia)
    {
    }

    public function ejemplo()
    {
        // Obtener versículos
        $verse = $this->biblia->getVerse('Génesis', 1, 1);
        
        // Buscar
        $results = $this->biblia->searchVerses('amor');
        
        // Capítulo completo
        $chapter = $this->biblia->getChapter('Salmos', 23);
        
        return response()->json($verse);
    }
}
```

### En una vista Blade

```blade
<div>
    @foreach($versiculos as $versiculo)
        <p>{{ $versiculo->getReferencia() }}: {{ $versiculo->texto }}</p>
    @endforeach
</div>
```

### En JavaScript

```javascript
// Obtener versículos
async function obtenerVerse() {
    const response = await fetch('/api/biblia/verse/Génesis/1/1');
    const data = await response.json();
    console.log(data);
}

// Buscar
async function buscar(palabra) {
    const response = await fetch(`/api/biblia/search?q=${palabra}`);
    const data = await response.json();
    console.log(data);
}
```

## 📁 Estructura de archivos

```
app/
├── Services/
│   └── BibliaDigitalService.php    # Servicio principal
├── Http/Controllers/
│   └── BibliaDigitalController.php # Controlador API
└── Models/
    └── Versiculo.php               # Modelo (opcional)

database/
├── migrations/
│   └── 2026_05_16_000000_create_versiculos_table.php
└── seeders/
    └── VersiculoSeeder.php

routes/
└── api.php                         # Rutas API
```

## 🔐 Seguridad

La API de A Bíblia Digital es pública y no requiere autenticación. Sin embargo, se recomienda:

```php
// Agregar autenticación a las rutas si lo deseas
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('biblia')->group(function () {
        // tus rutas aquí
    });
});
```

## ⚙️ Configuración

### Variables de entorno (.env)

```env
# Configuración automática (opcional)
BIBLIA_DIGITAL_API_URL=https://api.abiblia.org/graphql
BIBLIA_CACHE_EXPIRATION=86400
```

### Personalizar el servicio

Edita [app/Services/BibliaDigitalService.php](app/Services/BibliaDigitalService.php) para:

- Cambiar la URL de la API
- Ajustar tiempo de caché
- Agregar más métodos GraphQL

## 🧪 Pruebas

### Probar el servicio

```php
php artisan tinker

> $biblia = new App\Services\BibliaDigitalService();
> $verse = $biblia->getVerse('Génesis', 1, 1);
> dd($verse);
```

### Probar la API

```bash
# En otra terminal
php artisan serve

# Luego visita
http://localhost:8000/api/biblia/books
```

## 🐛 Solución de problemas

### Error: "Error al obtener los libros"

Verifica que:
- Tengas conexión a Internet
- La API esté disponible: https://api.abiblia.org/graphql
- Guzzle esté correctamente instalado

### Cache muy lleno

Limpia el caché:

```bash
php artisan cache:clear
# O usa el endpoint
POST /api/biblia/cache/clear
```

## 📚 Referencias

- [A Bíblia Digital](https://www.abiblia.org)
- [GraphQL](https://graphql.org)
- [Laravel Services](https://laravel.com/docs/services)
- [Guzzle HTTP](http://guzzlephp.org)

## 📝 Notas

- Los resultados se cachean por 24 horas
- Ideal para aplicaciones de estudio bíblico
- Compatible con todas las versiones modernas de Laravel

## 🤝 Soporte

Para problemas con la API de A Bíblia Digital, consulta su documentación oficial:
https://www.abiblia.org/api

## 📄 Licencia

Esta integración es parte de tu proyecto de administración de cursos bíblicos.

---

**¡Listo para usar!** 🎉

Prueba ahora mismo:
```bash
curl http://localhost:8000/api/biblia/books
```
