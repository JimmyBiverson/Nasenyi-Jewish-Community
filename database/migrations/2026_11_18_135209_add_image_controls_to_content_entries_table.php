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
        Schema::table('content_entries', function (Blueprint $table) {
            $table->string('header_image', 2048)->nullable();
            $table->unsignedTinyInteger('image_position_x')->nullable();
            $table->unsignedTinyInteger('image_position_y')->nullable();
            $table->json('body_image_overrides')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_entries', function (Blueprint $table) {
            $table->dropColumn(['header_image', 'image_position_x', 'image_position_y', 'body_image_overrides']);
        });
    }
};
