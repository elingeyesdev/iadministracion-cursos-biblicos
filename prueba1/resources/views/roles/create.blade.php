<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Crear Rol</h1>
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <input type="text" name="name" placeholder="Nombre del rol" class="border p-2 rounded w-full">
            @error('name') <p class="text-red-500">{{ $message }}</p> @enderror
            <button class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Guardar</button>
        </form>
    </div>
</x-app-layout>