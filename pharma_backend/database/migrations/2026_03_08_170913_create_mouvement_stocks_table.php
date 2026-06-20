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
        Schema::create('mouvement_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicament_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['entree', 'sortie', 'inventaire']);
            $table->integer('quantite');
            $table->integer('stock_avant');
            $table->integer('stock_apres');
            $table->string('reference')->nullable();
            $table->text('motif')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvement_stocks');
    }
};
