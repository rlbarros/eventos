<x-layouts::auth :title="__('Redefinição de Senha')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Redefinição de Senha')" :description="__('Por favor insira sua nova senha abaixo')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Email')"
                type="email"
                required
                autocomplete="email" />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Senha')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Senha')"
                viewable />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirme a Senha')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirme a Senha')"
                viewable />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('Redefinir Senha') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>