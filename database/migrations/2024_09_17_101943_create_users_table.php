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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone',20);
            $table->string('email')->nullable();
            $table->enum('role',['user','agent','admin'])->default('user');
            $table->string('password');
            $table->string('name',200)->nullable();
            $table->string('user_name',20)->unique();
            $table->string('refer_code',20)->nullable();
            $table->decimal('balance')->default(200);
            $table->string('currency',10)->default('BDT');
            $table->string('country')->default('BD');
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->date('dob');
            $table->string('profile')->nullable();
            $table->string('agent_id',50)->nullable();
            $table->string('manager_id',50)->nullable();
            $table->string('reference_id',50)->nullable();
            $table->enum('is_verified',['0','1'])->default('0');
            $table->enum('is_blocked',['0','1'])->default('0');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
