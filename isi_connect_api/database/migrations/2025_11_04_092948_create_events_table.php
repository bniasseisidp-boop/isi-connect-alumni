<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/..._create_events_table.php

public function up(): void
{
    Schema::create('events', function (Blueprint $table) {
        $table->id();

        // Qui organise/poste l'événement ?
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); 

        $table->string('title'); // Nom de l'événement
        $table->text('description');
        $table->string('location'); // Ex: "En ligne (Zoom)" ou "ISI SUPTECH, Dakar"
        $table->dateTime('starts_at'); // Date et heure de début
        $table->dateTime('ends_at')->nullable(); // Date et heure de fin (optionnel)
        $table->string('cover_image_url')->nullable(); // Une image de couverture

        $table->timestamps(); // created_at et updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
