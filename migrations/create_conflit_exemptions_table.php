<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConflictExemptionsTable extends Migration
{
    public function up()
    {
        Schema::create('conflict_exemptions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // student, teacher, room
            $table->string('entity_id'); // e.g., s1, t1, r1
            $table->string('conflict_type'); // schedule, capacity
            $table->text('reason');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Prevent duplicates
            $table->unique(['type', 'entity_id', 'conflict_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conflict_exemptions');
    }
}