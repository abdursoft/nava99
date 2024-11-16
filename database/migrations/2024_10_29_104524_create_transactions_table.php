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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('txnType',['DEBIT','CREDIT']);
            $table->string('roundId',200)->nullable();
            $table->decimal('amount');
            $table->string('currency',9)->default('BDT');
            $table->string('gameId',200)->nullable();
            $table->string('betId',200)->nullable();
            $table->longText('options')->nullable();
            $table->json('jpContributions');
            $table->string('device',300)->default('MOBILE');
            $table->string('clientType')->nullable();
            $table->string('clientRoundId')->nullable();
            $table->string('category',300)->nullable();
            $table->text('created')->nullable();
            $table->enum('completed',['true','false'])->default('false');
            $table->enum('status',['Pending','Completed','Canceled','Hold','Adjustment'])->default('Pending');

            // relation with user table
            $table->foreignUuid('playerId')->references('id')->on('users')->cascadeOnUpdate()->restrictOnDelete();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
