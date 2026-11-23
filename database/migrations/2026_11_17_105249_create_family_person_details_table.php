
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_person_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_person_id')
                ->unique()
                ->constrained('family_persons')
                ->restrictOnDelete();

            $table->text('home_address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('occupation', 255)->nullable();
            $table->string('education', 255)->nullable();
            $table->text('hobbies')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_person_details');
    }
};
