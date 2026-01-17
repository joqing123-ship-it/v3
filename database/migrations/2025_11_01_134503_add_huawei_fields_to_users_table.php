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
            $table->string('huawei_open_id')->nullable()->unique()->after('email');
            $table->string('huawei_union_id')->nullable()->after('huawei_open_id');
            $table->string('display_name')->nullable()->after('email');
            $table->string('avatar_uri')->nullable()->after('display_name');
            $table->enum('auth_provider', ['email', 'huawei'])->default('email')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['huawei_open_id', 'huawei_union_id', 'display_name', 'avatar_uri', 'auth_provider']);
        });
    }
};
