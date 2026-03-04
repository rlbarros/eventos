<?php

namespace App\Livewire\EventSites;

use App\Models\EventSite;
use Livewire\Component;
use Livewire\Attributes\{Layout};

new #[Layout('layouts.app')]
class extends Component
{
    public array $eventSites = [];

    public function mount(): void
    {
        $this->eventSites = EventSite::all()->toArray();
    }
}; ?>

<section>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Locais de Evento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Locais de Evento') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ __("Cadastro de chácaras, estâncias e espeços de recepção de onde serão realizados os eventos da IEA.") }}
                        </p>
                    </header>

                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b-2 border-gray-200">
                                <tr>
                                    <th class="px-4 py-2 font-semibold">ID</th>
                                    <th class="px-4 py-2 font-semibold">Nome</th>
                                    <th class="px-4 py-2 font-semibold">Cidade</th>
                                    <th class="px-4 py-2 font-semibold">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($eventSites as $site)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $site['id'] ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $site['name'] ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $site['city_id'] ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <button class="text-blue-600 hover:text-blue-800">Editar</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-gray-500">Nenhum local de evento cadastrado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>