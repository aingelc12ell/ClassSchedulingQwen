<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassesTable extends Migration
{
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->string('class_id')->primary();
            $table->string('subject_id');
            $table->string('teacher_id');
            $table->string('room_id');
            $table->unsignedBigInteger('time_slot_id');
            $table->string('day', 3); // Mon, Tue, etc.
            $table->string('term');
            $table->boolean('is_override')->default(false); // true if manually edited

            $table->timestamps();

            // Foreign keys (optional, if enforced at DB level)
            $table->foreign('time_slot_id')->references('id')->on('time_slots')->onDelete('cascade');
            $table->index(['subject_id', 'teacher_id', 'room_id', 'term', 'day']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('classes');
    }
}