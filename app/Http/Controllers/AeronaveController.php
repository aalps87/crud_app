<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAeronaveRequest;
use App\Http\Requests\UpdateAeronaveRequest;
use App\Models\Aeronave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AeronaveController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-aeronave|criar-aeronave|editar-aeronave|excluir-aeronave', only: ['index', 'show']),
            new Middleware('permission:criar-aeronave', only: ['create', 'store']),
            new Middleware('permission:editar-aeronave', only: ['edit', 'update']),
            new Middleware('permission:excluir-aeronave', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $filtro = in_array($request->query('filtro'), ['publicas', 'privadas'], true) ? $request->query('filtro') : 'todas';

        $query = Aeronave::with('publicacao')->latest('id');
        if ($filtro === 'publicas') {
            $query->publicas();
        } elseif ($filtro === 'privadas') {
            $query->whereDoesntHave('publicacao', fn ($q) => $q->where('publico', true));
        }

        $busca = trim((string) $request->query('busca', ''));
        if ($busca !== '') {
            $query->where(fn ($q) => $q->where('prefixo', 'like', "%{$busca}%")->orWhere('modelo', 'like', "%{$busca}%"));
        }

        $total = Aeronave::count();
        $publicas = Aeronave::publicas()->count();

        return view('products.index', [
            'aeronaves' => $query->paginate(9)->withQueryString(),
            'filtro' => $filtro,
            'busca' => $busca,
            'contagens' => ['todas' => $total, 'publicas' => $publicas, 'privadas' => $total - $publicas],
        ]);
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(StoreAeronaveRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'is_public']);
        $foto = $request->hasFile('image') ? $this->storeImage($request->file('image')) : null;

        try {
            DB::transaction(function () use ($data, $foto, $request): void {
                $aeronave = Aeronave::create([...$data, 'foto' => $foto]);
                $aeronave->publicacao()->create(['publico' => $request->boolean('is_public')]);
            });
        } catch (Throwable $exception) {
            if ($foto) {
                File::delete(public_path($foto));
            }
            throw $exception;
        }

        return redirect()->route('aeronaves.index')->withSuccess('Aeronave adicionada ao inventário.');
    }

    public function show(Aeronave $aeronave): View
    {
        return view('products.show', compact('aeronave'));
    }

    public function edit(Aeronave $aeronave): View
    {
        return view('products.edit', compact('aeronave'));
    }

    public function update(UpdateAeronaveRequest $request, Aeronave $aeronave): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'is_public']);
        $oldFoto = $aeronave->foto;
        $newFoto = $request->hasFile('image') ? $this->storeImage($request->file('image')) : null;

        if ($newFoto) {
            $data['foto'] = $newFoto;
        }

        try {
            DB::transaction(function () use ($aeronave, $data, $request): void {
                $aeronave->update($data);
                $aeronave->publicacao()->updateOrCreate([], ['publico' => $request->boolean('is_public')]);
            });
        } catch (Throwable $exception) {
            if ($newFoto) {
                File::delete(public_path($newFoto));
            }
            throw $exception;
        }

        if ($newFoto && $oldFoto && $oldFoto !== $newFoto) {
            $aeronave->deleteImageFile($oldFoto);
        }

        return redirect()->back()->withSuccess('Aeronave atualizada com sucesso.');
    }

    public function destroy(Aeronave $aeronave): RedirectResponse
    {
        $aeronave->delete();

        return redirect()->route('aeronaves.index')->withSuccess('Aeronave e imagem excluídas com sucesso.');
    }

    private function storeImage(UploadedFile $image): string
    {
        $directory = public_path('assets/aeronaves');
        File::ensureDirectoryExists($directory);

        do {
            $fileName = Str::lower(Str::random(8)).'.'.$image->extension();
        } while (File::exists($directory.'/'.$fileName));

        $image->move($directory, $fileName);

        return 'assets/aeronaves/'.$fileName;
    }
}
