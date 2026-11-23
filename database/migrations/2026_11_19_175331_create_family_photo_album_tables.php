
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Family Photo Albums
        |--------------------------------------------------------------------------
        */

        Schema::create('family_photo_albums', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_group_id')
                ->constrained('family_groups')
                ->restrictOnDelete();

            $table->string('name', 150);
            $table->string('slug', 180);
            $table->text('description')->nullable();

            // Foreign key cover ditambahkan setelah tabel foto dibuat.
            $table->unsignedBigInteger('cover_photo_id')->nullable();

            $table->date('event_date')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status', 20)->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['family_group_id', 'slug'],
                'family_photo_albums_group_slug_unique'
            );

            $table->index(
                ['family_group_id', 'status', 'event_date'],
                'family_photo_albums_group_status_date_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Family Photos
        |--------------------------------------------------------------------------
        */

        Schema::create('family_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_photo_album_id')
                ->constrained('family_photo_albums')
                ->restrictOnDelete();

            $table->string('file_path', 500);
            $table->string('thumbnail_path', 500);
            $table->string('preview_path', 500)->nullable();

            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');

            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->text('caption')->nullable();
            $table->dateTime('taken_at')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['family_photo_album_id', 'created_at'],
                'family_photos_album_created_index'
            );

            $table->index(
                ['family_photo_album_id', 'taken_at'],
                'family_photos_album_taken_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Album Cover Foreign Key
        |--------------------------------------------------------------------------
        */

        Schema::table('family_photo_albums', function (Blueprint $table) {
            $table->foreign('cover_photo_id', 'family_photo_albums_cover_photo_fk')
                ->references('id')
                ->on('family_photos')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Family Photo Favorites
        |--------------------------------------------------------------------------
        */

        Schema::create('family_photo_favorites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_photo_id')
                ->constrained('family_photos')
                ->cascadeOnDelete();

            $table->foreignId('family_member_id')
                ->constrained('family_members')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->nullable();

            $table->unique(
                ['family_photo_id', 'family_member_id'],
                'family_photo_favorites_photo_member_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_photo_favorites');

        Schema::table('family_photo_albums', function (Blueprint $table) {
            $table->dropForeign('family_photo_albums_cover_photo_fk');
        });

        Schema::dropIfExists('family_photos');
        Schema::dropIfExists('family_photo_albums');
    }
};
