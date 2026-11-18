<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 20)->default('member');
        });

        Schema::create('content_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 12);
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('body');
            $table->text('summary')->nullable();
            $table->string('image', 2048)->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('social_image', 2048)->nullable();
            $table->json('hero_slides')->nullable();
            $table->string('status', 12)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->index(['type', 'status', 'published_at']);
        });

        Schema::create('gallery_images', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('path', 2048);
            $table->string('alt_text');
            $table->text('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('community_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('location')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('donation_causes', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('donation_causes');
        Schema::dropIfExists('community_events');
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('content_entries');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }
};
