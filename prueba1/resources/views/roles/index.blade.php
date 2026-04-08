<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Roles</h1>
        <a href="{{ route('roles.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Crear Rol</a>

        @if(session('success'))
            <p class="text-green-500 mt-2">{{ session('success') }}</p>
        @endif

        <table class="mt-4 w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Nombre</th>
                    <th class="p-2 border">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr>
                    <td class="p-2 border">{{ $role->id }}</td>
                    <td class="p-2 border">{{ $role->name }}</td>
                    <td class="p-2 border">
                        <a href="{{ route('roles.edit', $role) }}" class="bg-yellow-400 px-3 py-1 rounded">Editar</a>
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button class="bg-red-500 text-white px-3 py-1 rounded">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>