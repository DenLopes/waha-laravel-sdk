<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waha_session_pins', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('waha_session_pins', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
