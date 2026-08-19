<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waha_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event')->nullable();
            $table->string('session')->nullable();
            $table->string('request_id')->nullable();
            $table->string('host_key')->nullable();
            $table->json('payload');
            $table->timestamps();

            $table->index('request_id');
            $table->index('host_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waha_webhook_events');
    }
};
