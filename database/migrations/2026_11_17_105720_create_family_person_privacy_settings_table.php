
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_person_privacy_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_person_id')
                ->unique()
                ->constrained('family_persons')
                ->restrictOnDelete();

            $table->string('default_field_visibility', 20)
                ->default('group_members');

            $table->boolean('show_profile_updates_in_timeline')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_person_privacy_settings');
    }
};
