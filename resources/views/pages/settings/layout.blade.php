<div class="flex items-start max-md:flex-col">
    <style>
        @media (width>=64rem) {
            .md\:w-55 {
                width: 220px
            }
        }
    </style>
    <div class="me-10 w-full pb-4 md:w-55">
        <flux:navlist aria-label="{{ __('Configurações') }}">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Perfil') }}</flux:navlist.item>
            <flux:navlist.item :href="route('user-password.edit')" wire:navigate>{{ __('Senha') }}</flux:navlist.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <flux:navlist.item :href="route('two-factor.show')" wire:navigate>{{ __('Autenticação em duas etapas') }}</flux:navlist.item>
            @endif
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Aparêhcia') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>