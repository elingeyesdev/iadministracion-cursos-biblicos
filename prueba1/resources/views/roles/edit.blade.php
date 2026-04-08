<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Editar Rol</h1>
        <form action="{{ route('roles.update', $role) }}" method="POST">
            @csrf @method('PUT')
            <input type="text" name="name" value="{{ $role->name }}" class="border p-2 rounded w-full">
            @error('name') <p class="text-red-500">{{ $message }}</p> @enderror
            <button class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Actualizar</button>
        </form>
    </div>
</x-app-layout>