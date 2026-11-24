<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('country', 100)->default('Belum dicatat');
            $table->text('biography')->default('');
            $table->timestamps();
        });
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160)->unique();
            $table->string('city', 100);
            $table->string('email', 180);
            $table->timestamps();
        });
        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->constrained('authors')->restrictOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->restrictOnDelete();
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->string('shelf', 40)->default('Belum dicatat');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->default('');
            $table->string('address', 500)->default('');
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('reserved_on')->nullable();
            $table->text('notes')->default('');
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->text('notes')->default('');
        });
        // Retain the legacy author string so the existing Blade/Livewire UI works.
        foreach (DB::table('books')->select('author')->distinct()->pluck('author') as $name) {
            $id = DB::table('authors')->insertGetId([
                'name' => $name,
                'country' => 'Belum dicatat',
                'biography' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('books')
                ->where('author', $name)
                ->update(['author_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('loans', fn (Blueprint $table) => $table->dropColumn('notes'));
        Schema::table('reservations', fn (Blueprint $table) => $table->dropColumn(['reserved_on', 'notes']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['phone', 'address']));
        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_id');
            $table->dropConstrainedForeignId('publisher_id');
            $table->dropColumn(['published_year', 'shelf']);
        });
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('authors');
    }
};
