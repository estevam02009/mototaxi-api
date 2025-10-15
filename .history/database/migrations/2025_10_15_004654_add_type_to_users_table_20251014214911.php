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
            $table->enum('type', ['passenger', 'driver'])->default('passenger')->after('email');
            // enum limita o valor a 'passenger' ou 'driver' (mototaxi)
            // default define o valor padrão como 'passenger'
            // after('email') define a posição da coluna 'type' logo após a coluna 'email'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
