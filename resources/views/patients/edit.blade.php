<x-app-layout title="Editar paciente">
    <div class="max-w-3xl">
        <h1 class="text-lg font-medium mb-6">Editar paciente</h1>

        <form method="POST" action="{{ route('patients.update', $patient) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @include('patients._form')

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-aura-olive text-white rounded px-4 py-2 text-sm font-medium hover:opacity-90">
                    Guardar cambios
                </button>
                <a href="{{ route('patients.show', $patient) }}" class="text-sm text-aura-gray hover:text-aura-gray-dark">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
