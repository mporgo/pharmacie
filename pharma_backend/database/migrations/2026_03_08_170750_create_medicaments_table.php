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
        Schema::create('medicaments', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('dosage')->nullable();
            $table->string('forme')->nullable(); // comprimé, sirop, injectable...
            $table->foreignId('categorie_id')->constrained()->onDelete('restrict');
            $table->foreignId('fournisseur_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('prix_achat', 10, 2)->default(0);
            $table->decimal('prix_vente', 10, 2)->default(0);
            $table->integer('stock_actuel')->default(0);
            $table->integer('stock_minimum')->default(5);
            $table->string('code_barre')->nullable()->unique();
            $table->date('date_expiration')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['nom', 'code_barre']);
            $table->index('categorie_id');
            $table->index('date_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicaments');
    }
};
