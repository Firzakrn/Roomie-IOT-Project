<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('sensor_status', 30)->nullable();
            $table->json('sensor_payload')->nullable();
            $table->timestamp('sensor_updated_at')->nullable();
        });

        Schema::table('room_sessions', function (Blueprint $table) {
            $table->unique('reservation_id');
        });
    }

    public function down(): void
    {
        Schema::table('room_sessions', function (Blueprint $table) {
            $table->dropUnique(['reservation_id']);
        });
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['sensor_status', 'sensor_payload', 'sensor_updated_at']);
        });
    }
};
