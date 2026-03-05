<x-layouts::auth :title="__('Confirmar senha')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirmar Senha')"
            :description="__('Este é um local seguro da aplicação. Por favor confirme sua senha antes de continuar.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('Senha')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Senha')"
                viewable />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Confirmar') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>