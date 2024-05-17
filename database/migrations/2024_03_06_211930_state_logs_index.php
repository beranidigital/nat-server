<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('state_logs', function (Blueprint $table) {
            $table->index('device');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('state_logs', function (Blueprint $table) {
            $table->dropIndex(['device']);
            $table->dropIndex(['created_at']);
        });
    }
};
