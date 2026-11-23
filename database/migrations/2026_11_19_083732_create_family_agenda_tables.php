
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_agendas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_group_id')
                ->constrained('family_groups')
                ->cascadeOnDelete();

            $table->string('title', 180);
            $table->string('category', 80)->nullable();
            $table->text('description')->nullable();
            $table->string('location', 255)->nullable();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_all_day')->default(false);

            $table->string('audience_type', 30)->default('all_members');
            $table->string('status', 30)->default('scheduled');

            $table->text('cancellation_reason')->nullable();

            // ID pengguna pembuat agenda.
            // Belum diberi foreign key karena perlu menyesuaikan
            // dengan skema autentikasi Family Portal.
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['family_group_id', 'starts_at'],
                'family_agendas_group_start_index'
            );

            $table->index(
                ['status', 'starts_at'],
                'family_agendas_status_start_index'
            );
        });

        Schema::create('family_agenda_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_agenda_id')
                ->constrained('family_agendas')
                ->cascadeOnDelete();

            $table->foreignId('family_member_id')
                ->constrained('family_members')
                ->cascadeOnDelete();

            $table->string('attendance_status', 30)->default('pending');
            $table->dateTime('response_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['family_agenda_id', 'family_member_id'],
                'family_agenda_participant_unique'
            );

            $table->index(
                ['family_member_id', 'attendance_status'],
                'family_agenda_participant_status_index'
            );
        });

        Schema::create('family_agenda_reminders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_agenda_id')
                ->constrained('family_agendas')
                ->cascadeOnDelete();

            $table->unsignedInteger('minutes_before');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['family_agenda_id', 'minutes_before'],
                'family_agenda_reminder_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_agenda_reminders');
        Schema::dropIfExists('family_agenda_participants');
        Schema::dropIfExists('family_agendas');
    }
};
