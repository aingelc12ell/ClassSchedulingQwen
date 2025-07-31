<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurriculumsTable extends Migration
{
    public function up()
    {
        Schema::create('curriculums', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('term'); // e.g., Fall2024, Spring2025
            $table->json('subject_ids'); // Array of subject IDs
            $table->timestamps();

            // Index for term queries
            $table->index('term');
        });
    }

    public function down()
    {
        Schema::dropIfExists('curriculums');
    }
}