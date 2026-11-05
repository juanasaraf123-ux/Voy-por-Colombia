<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contenido de la plataforma: geografía, multimedia, publicaciones de todas las secciones, rutas,
 * configuración y auditoría. Funciona igual en SQLite, MySQL/MariaDB y PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80);
            $t->string('slug', 80)->unique();
            $t->string('color', 9)->default('#0f9d77');
            $t->string('summary', 400)->nullable();
            $t->integer('sort_order')->default(0);
        });

        Schema::create('collections', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('slug', 140)->unique();
            $t->string('description', 400)->nullable();
            $t->integer('sort_order')->default(0);
            $t->timestamp('created_at')->nullable();
        });

        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name', 100);
            $t->string('slug', 100)->unique();
            $t->string('iso_code', 10)->nullable();
            $t->string('capital', 100)->nullable();
            $t->string('summary', 500)->nullable();
            $t->unsignedBigInteger('cover_media_id')->nullable();
            $t->string('search_name', 120)->default('');
        });

        Schema::create('municipalities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained()->cascadeOnDelete();
            $t->string('name', 120);
            $t->string('slug', 140);
            $t->decimal('lat', 10, 6)->nullable();
            $t->decimal('lng', 10, 6)->nullable();
            $t->string('search_name', 140)->default('')->index();
            $t->unique(['department_id', 'slug']);
        });

        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->string('kind', 12);
            $t->string('path', 255)->nullable();
            $t->string('mime', 80)->nullable();
            $t->string('original_name', 190)->nullable();
            $t->unsignedInteger('size_bytes')->default(0);
            $t->unsignedInteger('width')->nullable();
            $t->unsignedInteger('height')->nullable();
            $t->text('variants')->nullable();
            $t->string('embed_provider', 20)->nullable();
            $t->string('embed_id', 40)->nullable();
            $t->string('title', 190)->nullable();
            $t->string('alt_text', 255)->nullable();
            $t->string('caption', 500)->nullable();
            $t->string('credit', 190)->nullable();
            $t->string('license', 120)->nullable();
            $t->string('source_url', 500)->nullable();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('collection_id')->nullable()->constrained()->nullOnDelete();
            $t->string('gallery_section', 20)->nullable();
            $t->boolean('in_gallery')->default(false);
            $t->boolean('gallery_featured')->default(false);
            $t->boolean('is_demo')->default(false);
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['in_gallery', 'kind', 'created_at']);
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('section', 20);
            $t->string('name', 80);
            $t->string('slug', 90);
            $t->string('icon', 30)->nullable();
            $t->string('color', 9)->nullable();
            $t->integer('sort_order')->default(0);
            $t->unique(['section', 'slug']);
        });

        Schema::create('sections', function (Blueprint $t) {
            $t->id();
            $t->string('section_key', 20)->unique();
            $t->string('title', 80);
            $t->string('tagline', 160)->nullable();
            $t->string('description', 500)->nullable();
            $t->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $t->integer('sort_order')->default(0);
            $t->boolean('show_on_home')->default(true);
        });

        Schema::create('entries', function (Blueprint $t) {
            $t->id();
            $t->string('section', 20);
            $t->string('title', 190);
            $t->string('slug', 190);
            $t->string('summary', 400)->nullable();
            $t->longText('body')->nullable();
            $t->string('status', 12)->default('draft');
            $t->timestamp('published_at')->nullable();
            $t->foreignId('department_id')->nullable()->index()->constrained()->nullOnDelete();
            $t->foreignId('municipality_id')->nullable()->index()->constrained()->nullOnDelete();
            $t->decimal('lat', 10, 6)->nullable();
            $t->decimal('lng', 10, 6)->nullable();
            $t->boolean('show_on_map')->default(true);
            $t->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $t->boolean('is_featured')->default(false);
            $t->integer('featured_order')->default(0);
            $t->boolean('is_hidden_gem')->default(false);
            $t->timestamp('event_start')->nullable()->index();
            $t->timestamp('event_end')->nullable();
            $t->string('author_name', 120)->nullable();
            $t->text('meta')->nullable();
            $t->text('search_text')->nullable();
            $t->boolean('is_demo')->default(false);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['section', 'slug']);
            $t->index(['section', 'status', 'published_at']);
        });

        Schema::create('entry_categories', function (Blueprint $t) {
            $t->foreignId('entry_id')->constrained()->cascadeOnDelete();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->primary(['entry_id', 'category_id']);
        });

        Schema::create('entry_media', function (Blueprint $t) {
            $t->foreignId('entry_id')->constrained()->cascadeOnDelete();
            $t->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $t->integer('sort_order')->default(0);
            $t->primary(['entry_id', 'media_id']);
        });

        Schema::create('routes', function (Blueprint $t) {
            $t->id();
            $t->string('title', 190);
            $t->string('slug', 190)->unique();
            $t->string('summary', 400)->nullable();
            $t->longText('body')->nullable();
            $t->string('duration', 60)->nullable();
            $t->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('status', 12)->default('draft');
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_demo')->default(false);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('route_stops', function (Blueprint $t) {
            $t->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $t->foreignId('entry_id')->constrained()->cascadeOnDelete();
            $t->integer('sort_order')->default(0);
            $t->string('note', 255)->nullable();
            $t->primary(['route_id', 'entry_id']);
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('setting_key', 80)->primary();
            $t->text('setting_value')->nullable();
            $t->timestamp('updated_at')->nullable();
        });

        Schema::create('audit_log', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action', 30);
            $t->string('entity_type', 30);
            $t->unsignedBigInteger('entity_id')->nullable();
            $t->string('summary', 255);
            $t->string('ip', 45)->nullable();
            $t->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        foreach (['audit_log', 'settings', 'route_stops', 'routes', 'entry_media', 'entry_categories', 'entries', 'sections', 'categories', 'media', 'municipalities', 'departments', 'collections', 'regions'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
