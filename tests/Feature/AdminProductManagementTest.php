<?php

namespace Tests\Feature;

use App\Models\Aeronave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_aeronaves_table_has_the_requested_structure(): void
    {
        $columns = collect(DB::select("PRAGMA table_info('aeronaves')"));

        $this->assertSame(['id', 'prefixo', 'modelo', 'tripulacao', 'data_fab', 'foto'], $columns->pluck('name')->all());
        $this->assertSame('VARCHAR(10)', $columns->firstWhere('name', 'prefixo')->type);
        $this->assertSame('VARCHAR(30)', $columns->firstWhere('name', 'modelo')->type);
        $this->assertSame('VARCHAR(30)', $columns->firstWhere('name', 'foto')->type);
    }

    public function test_landing_page_only_lists_public_aircraft(): void
    {
        $this->createAeronave(['prefixo' => 'PUB-01', 'modelo' => 'Aeronave pública'], true);
        $this->createAeronave(['prefixo' => 'PRI-01', 'modelo' => 'Aeronave privada'], false);

        $this->get('/')->assertOk()->assertSee('Aeronave pública')->assertDontSee('Aeronave privada');
    }

    public function test_crud_requires_an_authenticated_user(): void
    {
        $this->get('/aeronaves')->assertRedirect(route('landing', ['login' => 1]));
        $this->post('/login', ['email' => 'adm@gmail.com', 'password' => 'adm123'])
            ->assertRedirect(route('aeronaves.index'));
        $this->assertAuthenticatedAs($this->admin());
        $this->get('/aeronaves')->assertOk();
    }

    public function test_other_credentials_cannot_open_the_crud(): void
    {
        $this->post('/login', ['email' => 'outro@example.com', 'password' => 'adm123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_upload_and_delete_an_aircraft_image(): void
    {
        $this->actingAs($this->admin())->post('/aeronaves', [
            ...$this->aeronaveData(['is_public' => true]),
            'image' => $this->fakePng('aeronave.png'),
        ])->assertRedirect(route('aeronaves.index'));

        $aeronave = Aeronave::firstOrFail();
        $this->assertTrue(File::exists(public_path($aeronave->foto)));
        $this->assertStringStartsWith('assets/aeronaves/', $aeronave->foto);
        $this->assertLessThanOrEqual(30, strlen($aeronave->foto));

        $this->actingAs($this->admin())
            ->delete(route('aeronaves.destroy', $aeronave))
            ->assertRedirect(route('aeronaves.index'));

        $this->assertDatabaseMissing('aeronaves', ['id' => $aeronave->id]);
        $this->assertDatabaseMissing('aeronave_publicacoes', ['aeronave_id' => $aeronave->id]);
        $this->assertFalse(File::exists(public_path($aeronave->foto)));
    }

    public function test_admin_can_create_an_aircraft_without_an_image(): void
    {
        $this->actingAs($this->admin())->get(route('aeronaves.create'))
            ->assertOk()->assertSee('Selecionar imagem')->assertSee('data-image-preview', false);

        $this->actingAs($this->admin())->post('/aeronaves', $this->aeronaveData())
            ->assertRedirect(route('aeronaves.index'));

        $this->assertDatabaseHas('aeronaves', ['prefixo' => 'PT-ABC', 'foto' => null]);
    }

    public function test_registration_uses_only_a_date_and_requires_positive_crew(): void
    {
        $this->actingAs($this->admin())->get(route('aeronaves.create'))
            ->assertOk()
            ->assertSee('type="date"', false)
            ->assertDontSee('type="datetime-local"', false)
            ->assertSee('data-positive-integer', false)
            ->assertSee('max="'.now()->format('Y-m-d').'"', false);

        $this->actingAs($this->admin())->post('/aeronaves', $this->aeronaveData([
            'tripulacao' => 0,
        ]))->assertSessionHasErrors('tripulacao');

        $this->actingAs($this->admin())->post('/aeronaves', $this->aeronaveData([
            'tripulacao' => -1,
            'prefixo' => 'PT-NEG',
        ]))->assertSessionHasErrors('tripulacao');

        $this->actingAs($this->admin())->post('/aeronaves', $this->aeronaveData([
            'tripulacao' => '1.5',
            'prefixo' => 'PT-DEC',
        ]))->assertSessionHasErrors('tripulacao');

        $this->actingAs($this->admin())->post('/aeronaves', $this->aeronaveData([
            'tripulacao' => '1,5',
            'prefixo' => 'PT-VIR',
        ]))->assertSessionHasErrors('tripulacao');

        $this->actingAs($this->admin())->post('/aeronaves', $this->aeronaveData([
            'data_fab' => now()->addDay()->format('Y-m-d'),
            'prefixo' => 'PT-FUT',
        ]))->assertSessionHasErrors('data_fab');

        $this->assertDatabaseCount('aeronaves', 0);
    }

    public function test_replacing_an_image_removes_the_previous_file(): void
    {
        $this->actingAs($this->admin())->post('/aeronaves', [
            ...$this->aeronaveData(), 'image' => $this->fakePng('original.png'),
        ]);
        $aeronave = Aeronave::firstOrFail();
        $oldPath = $aeronave->foto;

        $this->actingAs($this->admin())->put(route('aeronaves.update', $aeronave), [
            ...$this->aeronaveData(['modelo' => 'Embraer Phenom 300']),
            'image' => $this->fakePng('nova.png'),
        ])->assertRedirect();

        $aeronave->refresh();
        $this->assertNotSame($oldPath, $aeronave->foto);
        $this->assertFalse(File::exists(public_path($oldPath)));
        $this->assertTrue(File::exists(public_path($aeronave->foto)));
        $aeronave->delete();
    }

    public function test_adding_the_first_image_during_an_update_keeps_the_new_file(): void
    {
        $aeronave = $this->createAeronave();
        $this->actingAs($this->admin())->put(route('aeronaves.update', $aeronave), [
            ...$this->aeronaveData(), 'image' => $this->fakePng('primeira.png'),
        ])->assertRedirect();

        $aeronave->refresh();
        $this->assertTrue(File::exists(public_path($aeronave->foto)));
        $aeronave->delete();
    }

    public function test_upload_rejects_unsupported_image_formats(): void
    {
        $this->actingAs($this->admin())->post('/aeronaves', [
            ...$this->aeronaveData(),
            'image' => UploadedFile::fake()->create('aeronave.gif', 20, 'image/gif'),
        ])->assertSessionHasErrors('image');
        $this->assertDatabaseCount('aeronaves', 0);
    }

    private function admin(): User
    {
        return User::where('email', 'adm@gmail.com')->firstOrFail();
    }

    private function createAeronave(array $overrides = [], bool $publico = false): Aeronave
    {
        $data = $this->aeronaveData($overrides);
        unset($data['is_public']);
        $aeronave = Aeronave::create($data);
        $aeronave->publicacao()->create(['publico' => $publico]);

        return $aeronave;
    }

    private function aeronaveData(array $overrides = []): array
    {
        return array_merge([
            'prefixo' => 'PT-ABC',
            'modelo' => 'Cessna 172 Skyhawk',
            'tripulacao' => 4,
            'data_fab' => '2020-06-15',
            'is_public' => false,
        ], $overrides);
    }

    private function fakePng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true
        ));
    }
}
