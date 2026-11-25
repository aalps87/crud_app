<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_landing_offers_account_creation(): void
    {
        $this->get(route('landing'))->assertOk()->assertSee(route('register'))->assertSee('Criar conta');
    }

    public function test_new_account_gets_usuario_role_and_is_logged_in(): void
    {
        $this->post(route('register'), [
            'name' => 'Nova Pessoa',
            'email' => 'Nova@Exemplo.com',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
        ])->assertRedirect(route('aeronaves.index'));

        $user = User::where('email', 'nova@exemplo.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(['Usuário'], $user->getRoleNames()->all());

        $this->get(route('aeronaves.index'))->assertOk();
        $this->get(route('aeronaves.create'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_registration_cannot_escalate_role(): void
    {
        $this->post(route('register'), [
            'name' => 'Espertinho',
            'email' => 'espertinho@exemplo.com',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
            'roles' => ['Super Admin'],
        ]);

        $this->assertSame(['Usuário'], User::where('email', 'espertinho@exemplo.com')->firstOrFail()->getRoleNames()->all());
    }

    public function test_registration_validates_in_portuguese_in_its_own_bag(): void
    {
        $this->from(route('landing'))->post(route('register'), [
            'name' => '',
            'email' => 'adm@gmail.com',
            'password' => 'curta',
            'password_confirmation' => 'outra',
        ])->assertRedirect(route('landing'))
            ->assertSessionHasErrors(['name', 'email', 'password'], null, 'register');

        $this->assertGuest();
        $this->get(route('landing'))->assertSee('Este e-mail já está cadastrado.')->assertSee('data-open-on-load="true"', false);
    }

    public function test_logged_in_user_cannot_register_again(): void
    {
        $this->actingAs(User::where('email', 'usuario@aeris.com')->first())
            ->post(route('register'), ['name' => 'x'])
            ->assertRedirect(route('aeronaves.index'));
    }
}
