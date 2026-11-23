
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Siapkan indeks pengganti untuk foreign key family_photo_id.
        Schema::table('family_photo_favorites', function (Blueprint $table) {
            $table->index(
                'family_photo_id',
                'family_photo_favorites_photo_temp_index'
            );
        });

        // Hapus unique index lama setelah indeks pengganti tersedia.
        Schema::table('family_photo_favorites', function (Blueprint $table) {
            $table->dropUnique(
                'family_photo_favorites_photo_member_unique'
            );
        });

        // Tambahkan identitas user untuk favorit superadmin.
        Schema::table('family_photo_favorites', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('family_photo_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('family_member_id')
                ->nullable()
                ->change();
        });

        // Buat unique index untuk masing-masing jenis identitas.
        Schema::table('family_photo_favorites', function (Blueprint $table) {
            $table->unique(
                ['family_photo_id', 'family_member_id'],
                'family_photo_favorites_photo_member_unique'
            );

            $table->unique(
                ['family_photo_id', 'user_id'],
                'family_photo_favorites_photo_user_unique'
            );

            $table->dropIndex(
                'family_photo_favorites_photo_temp_index'
            );
        });
    }

    public function down(): void
    {
        // Hapus favorit berbasis user karena tidak bisa dikembalikan
        // menjadi favorit berbasis family_member secara otomatis.
        Schema::table('family_photo_favorites', function (Blueprint $table) {
            $table->dropUnique(
                'family_photo_favorites_photo_user_unique'
            );

            $table->dropUnique(
                'family_photo_favorites_photo_member_unique'
            );
        });

        \Illuminate\Support\Facades\DB::table('family_photo_favorites')
            ->whereNull('family_member_id')
            ->delete();

        Schema::table('family_photo_favorites', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->unsignedBigInteger('family_member_id')
                ->nullable(false)
                ->change();

            $table->unique(
                ['family_photo_id', 'family_member_id'],
                'family_photo_favorites_photo_member_unique'
            );
        });
    }
};
