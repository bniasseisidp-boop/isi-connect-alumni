<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/..._create_forum_replies_table.php

public function up(): void
{
    Schema::create('forum_replies', function (Blueprint $table) {
        $table->id();

        // Lie la réponse au sujet principal
        $table->foreignId('forum_thread_id')->constrained()->onDelete('cascade');

        // Lie la réponse à son auteur
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        $table->text('body'); // Contenu de la réponse
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_replies');
    }
};
