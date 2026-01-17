<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('push_token', 500)->nullable()->after('remember_token');
            $table->string('push_platform', 50)->nullable()->after('push_token');
            $table->index('push_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['push_token']);
            $table->dropColumn(['push_token', 'push_platform']);
        });
    }
};
