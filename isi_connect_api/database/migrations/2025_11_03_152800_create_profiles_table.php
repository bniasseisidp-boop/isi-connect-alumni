<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/..._create_profiles_table.php

public function up(): void
{
    Schema::create('profiles', function (Blueprint $table) {
        $table->id();

        // Clé étrangère pour lier à l'utilisateur
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        $table->text('bio')->nullable();
        $table->string('job_title')->nullable(); // Poste
        $table->string('company_name')->nullable(); // Entreprise
        $table->string('city')->nullable(); // Ville
        $table->string('linkedin_url')->nullable();
        $table->string('profile_picture_url')->nullable();
        $table->boolean('is_visible')->default(true); // Pour l'annuaire

        $table->timestamps(); // Crée created_at et updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
