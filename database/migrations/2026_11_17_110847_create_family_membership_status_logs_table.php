
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_membership_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_member_id')
                ->constrained('family_members')
                ->restrictOnDelete();

            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('reason')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['family_member_id', 'created_at'],
                'membership_status_logs_history_index'
            );
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE family_membership_status_logs
                ADD CONSTRAINT membership_status_logs_valid_from_status
                CHECK (
                    from_status IS NULL OR from_status IN (
                        'pending', 'active', 'inactive', 'left', 'rejected'
                    )
                )
            ");

            DB::statement("
                ALTER TABLE family_membership_status_logs
                ADD CONSTRAINT membership_status_logs_valid_to_status
                CHECK (
                    to_status IN (
                        'pending', 'active', 'inactive', 'left', 'rejected'
                    )
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('family_membership_status_logs');
    }
};
