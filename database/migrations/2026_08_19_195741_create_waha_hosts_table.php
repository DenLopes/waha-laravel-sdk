<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waha_hosts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('base_url');
            $table->string('api_key')->nullable();
            $table->string('api_key_header')->default('X-Api-Key');
            $table->string('default_session')->nullable();
            $table->string('mode')->default('admin_fallback');
            $table->json('session_keys')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waha_hosts');
    }
};
