<?php

namespace Tests\Feature;

use App\Models\Aeronave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_usuario_can_only_view_aircraft(): void
    {
        $usuario = $this->user('usuario@aeris.com');
        $aeronave = $this->createAeronave();

        $this->actingAs($usuario)->get(route('aeronaves.index'))
            ->assertOk()->assertDontSee(route('aeronaves.create'));
        $this->actingAs($usuario)->get(route('aeronaves.show', $aeronave))->assertOk();
        $this->actingAs($usuario)->get(route('aeronaves.create'))->assertForbidden();
        $this->actingAs($usuario)->delete(route('aeronaves.destroy', $aeronave))->assertForbidden();
        $this->actingAs($usuario)->get(route('users.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('roles.index'))->assertForbidden();
    }

    public function test_gerente_de_frota_manages_aircraft_but_not_users(): void
    {
        $gerente = $this->user('gerente@aeris.com');

        $this->actingAs($gerente)->get(route('aeronaves.index'))->assertOk();
        $this->actingAs($gerente)->get(route('aeronaves.create'))->assertOk();
        $this->actingAs($gerente)->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_manages_users_but_not_roles(): void
    {
        $admin = $this->user('admin@aeris.com');

        $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee('Usuários');
        $this->actingAs($admin)->get(route('roles.index'))->assertForbidden();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nova Pessoa',
            'email' => 'Nova@Aeris.com',
            'password' => 'senha1234',
            'password_confirmation' => 'senha1234',
            'roles' => ['Usuário'],
        ])->assertRedirect(route('users.index'));

        $nova = User::where('email', 'nova@aeris.com')->firstOrFail();
        $this->assertTrue($nova->hasRole('Usuário'));

        $this->actingAs($admin)->get(route('users.edit', $nova))->assertOk()->assertSee('Nova Pessoa');
        $this->actingAs($admin)->put(route('users.update', $nova), [
            'name' => 'Nova Pessoa',
            'email' => 'nova@aeris.com',
            'password' => '',
            'password_confirmation' => '',
            'roles' => ['Gerente de Frota'],
        ])->assertRedirect();
        $this->assertTrue($nova->fresh()->hasRole('Gerente de Frota'));

        $this->post(route('logout'));
        $this->post('/login', ['email' => 'nova@aeris.com', 'password' => 'senha1234'])
            ->assertRedirect(route('aeronaves.index'));
    }

    public function test_admin_cannot_grant_super_admin_or_touch_super_admins(): void
    {
        $admin = $this->user('admin@aeris.com');
        $superAdmin = $this->user('adm@gmail.com');

        $this->actingAs($admin)->get(route('users.create'))->assertOk()->assertDontSee('value="Super Admin"', false);
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Intruso',
            'email' => 'intruso@aeris.com',
            'password' => 'senha1234',
            'password_confirmation' => 'senha1234',
            'roles' => ['Super Admin'],
        ])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'intruso@aeris.com']);

        $this->actingAs($admin)->get(route('users.edit', $superAdmin))->assertForbidden();
        $this->actingAs($admin)->delete(route('users.destroy', $superAdmin))->assertForbidden();
    }

    public function test_user_form_validates_in_portuguese(): void
    {
        $this->actingAs($this->user('admin@aeris.com'))->post(route('users.store'), [
            'name' => 'Teste',
            'email' => 'admin@aeris.com',
            'password' => 'senha1234',
            'password_confirmation' => 'outra1234',
        ])->assertSessionHasErrors([
            'email' => 'Este e-mail já está cadastrado.',
            'password' => 'A confirmação da senha não confere.',
            'roles' => 'Selecione ao menos um papel.',
        ]);
    }

    public function test_users_cannot_delete_themselves(): void
    {
        $admin = $this->user('admin@aeris.com');

        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertForbidden();
        $this->assertModelExists($admin);
    }

    public function test_super_admin_manages_roles(): void
    {
        $superAdmin = $this->user('adm@gmail.com');

        $this->actingAs($superAdmin)->get(route('roles.index'))->assertOk()->assertSee('Gerente de Frota');
        $this->actingAs($superAdmin)->get(route('roles.create'))->assertOk()->assertSee('criar-aeronave');
        $this->actingAs($superAdmin)->get(route('roles.edit', Role::findByName('Admin')))->assertOk()->assertSee('checked', false);

        $this->actingAs($superAdmin)->post(route('roles.store'), [
            'name' => 'Auditor',
            'permissions' => [Role::findByName('Usuário')->permissions->first()->id],
        ])->assertRedirect(route('roles.index'));
        $this->assertTrue(Role::findByName('Auditor')->hasPermissionTo('ver-aeronave'));

        $this->actingAs($superAdmin)->delete(route('roles.destroy', Role::findByName('Auditor')))
            ->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['name' => 'Auditor']);
    }

    public function test_super_admin_role_is_protected(): void
    {
        $superAdmin = $this->user('adm@gmail.com');
        $role = Role::findByName('Super Admin');

        $this->actingAs($superAdmin)->get(route('roles.edit', $role))->assertForbidden();
        $this->actingAs($superAdmin)->delete(route('roles.destroy', $role))->assertForbidden();
        $this->assertModelExists($role);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function createAeronave(): Aeronave
    {
        $aeronave = Aeronave::create([
            'prefixo' => 'PT-ROL',
            'modelo' => 'Embraer Ipanema',
            'tripulacao' => 1,
            'data_fab' => '2019-03-10',
        ]);
        $aeronave->publicacao()->create(['publico' => false]);

        return $aeronave;
    }
}
