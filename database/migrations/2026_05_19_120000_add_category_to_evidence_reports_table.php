<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_reports', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('reporter_phone');
        });
    }

    public function down(): void
    {
        Schema::table('evidence_reports', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
