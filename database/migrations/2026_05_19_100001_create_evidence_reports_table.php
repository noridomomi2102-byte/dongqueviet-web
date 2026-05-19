<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reporter_name');
            $table->string('reporter_email')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->string('source_url')->nullable();
            $table->text('description');
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'processing', 'resolved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_reports');
    }
};
