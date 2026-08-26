<x-app-layout title="Inicio">
    <h1 class="text-2xl font-light text-aura-gray-dark mb-2">
        Hola, {{ auth()->user()->name }}
    </h1>
    <p class="text-aura-gray text-sm mb-8">
        Sesión iniciada como <span class="font-medium">{{ auth()->user()->role->label() }}</span>.
    </p>

    <div class="rounded-lg border border-aura-gray-light bg-white p-8 text-sm text-aura-gray">
        Los módulos clínicos (pacientes, historia clínica, consultas, odontograma, consentimientos)
        se habilitarán en los siguientes sprints.
    </div>
</x-app-layout>
