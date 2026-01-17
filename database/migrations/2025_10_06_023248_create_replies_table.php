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
        Schema::create('replies', function (Blueprint $table) {
        $table->id();
            $table->foreignId(column: 'user_id')
                ->constrained('users','id')
                ->onDelete('cascade');
            $table->foreignId('taged_user_id')
                ->constrained('users','id')
                ->onDelete('cascade');
            $table->foreignId(column: 'comment_id')
                ->constrained('comments','id')
                ->onDelete('cascade');
            $table->boolean('hide')->default(false);

            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replies');
    }
};
