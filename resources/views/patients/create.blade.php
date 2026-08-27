<x-app-layout title="Nuevo paciente">
    <div class="max-w-3xl">
        <h1 class="text-lg font-medium mb-6">Nuevo paciente</h1>

        <form method="POST" action="{{ route('patients.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @include('patients._form')

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-aura-olive text-white rounded px-4 py-2 text-sm font-medium hover:opacity-90">
                    Guardar paciente
                </button>
                <a href="{{ route('patients.index') }}" class="text-sm text-aura-gray hover:text-aura-gray-dark">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
