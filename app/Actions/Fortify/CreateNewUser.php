<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\AdministrationHost;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public const NOT_A_HOST = 'Só anfitriões cadastrados na administração podem criar conta. Use o e-mail do seu cadastro de anfitrião ou peça à secretaria para cadastrar você.';

    /**
     * Validate and create a newly registered user.
     *
     * Só quem é anfitrião ativo na administração (réplica `administration_hosts`, via data-sync)
     * pode criar conta, e a conta nasce presa ao usuário de lá.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $host = null;

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->after(function ($validator) use ($input, &$host) {
            if ($validator->errors()->has('email')) {
                return;
            }
            $host = AdministrationHost::activeForEmail($input['email'] ?? null);
            if (! $host) {
                $validator->errors()->add('email', self::NOT_A_HOST);
            } elseif (User::where('administration_user_id', $host->administration_user_id)->exists()) {
                $validator->errors()->add('email', 'Já existe uma conta para este anfitrião. Entre com ela ou redefina a senha.');
            }
        })->validate();

        return User::create([
            'administration_user_id' => $host->administration_user_id,
            'name' => $input['name'],
            'email' => $host->email,
            'password' => $input['password'],
        ]);
    }
}
