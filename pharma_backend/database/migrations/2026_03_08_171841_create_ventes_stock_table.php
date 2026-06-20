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
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->decimal('total', 10, 2);
            $table->decimal('remise', 10, 2)->default(0);
            $table->decimal('montant_paye', 10, 2);
            $table->decimal('monnaie', 10, 2)->default(0);
            $table->enum('statut', ['validee', 'annulee'])->default('validee');
            $table->string('client_nom')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventes_stock');
    }
};
