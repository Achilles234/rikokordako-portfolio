
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_group_activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_group_id')
                ->constrained('family_groups')
                ->restrictOnDelete();

            $table->string('action', 50);

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('reason')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['family_group_id', 'created_at'],
                'family_group_activity_history_index'
            );

            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_group_activity_logs');
    }
};
