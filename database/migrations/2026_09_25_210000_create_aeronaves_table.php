<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TABLE aeronaves (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                prefixo VARCHAR(10) NOT NULL UNIQUE,
                modelo VARCHAR(30) NOT NULL,
                tripulacao INT NOT NULL,
                data_fab DATETIME NOT NULL,
                foto VARCHAR(30) NULL
            )');
        } else {
            Schema::create('aeronaves', function (Blueprint $table) {
                $table->increments('id');
                $table->string('prefixo', 10)->unique();
                $table->string('modelo', 30);
                $table->integer('tripulacao');
                $table->dateTime('data_fab');
                $table->string('foto', 30)->nullable();
            });
        }

        Schema::create('aeronave_publicacoes', function (Blueprint $table) {
            $table->unsignedInteger('aeronave_id')->primary();
            $table->boolean('publico')->default(false);
            $table->foreign('aeronave_id')->references('id')->on('aeronaves')->cascadeOnDelete();
        });

        if (! Schema::hasTable('products')) {
            return;
        }

        foreach (DB::table('products')->orderBy('id')->get() as $product) {
            $foto = $this->migrateImage($product->image_path);
            $id = DB::table('aeronaves')->insertGetId([
                'prefixo' => mb_substr((string) $product->code, 0, 10),
                'modelo' => mb_substr((string) $product->name, 0, 30),
                'tripulacao' => max(1, (int) $product->quantity),
                'data_fab' => $product->created_at ?? now(),
                'foto' => $foto,
            ]);

            DB::table('aeronave_publicacoes')->insert([
                'aeronave_id' => $id,
                'publico' => (bool) $product->is_public,
            ]);
        }

        Schema::drop('products');
    }

    public function down(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('price', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        foreach (DB::table('aeronaves')->orderBy('id')->get() as $aeronave) {
            DB::table('products')->insert([
                'code' => $aeronave->prefixo,
                'name' => $aeronave->modelo,
                'quantity' => $aeronave->tripulacao,
                'price' => 0,
                'description' => null,
                'is_public' => (bool) DB::table('aeronave_publicacoes')->where('aeronave_id', $aeronave->id)->value('publico'),
                'image_path' => $aeronave->foto,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::dropIfExists('aeronave_publicacoes');
        Schema::dropIfExists('aeronaves');
    }

    private function migrateImage(?string $oldPath): ?string
    {
        if (! $oldPath || ! File::exists(public_path($oldPath))) {
            return null;
        }

        $extension = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
        $newPath = 'assets/aeronaves/'.Str::lower(Str::random(8)).'.'.$extension;
        File::ensureDirectoryExists(public_path('assets/aeronaves'));
        File::move(public_path($oldPath), public_path($newPath));

        return $newPath;
    }
};
