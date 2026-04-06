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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('type')->default('text')->after('sender_id'); // text, image, video, voice
            $table->string('file_path')->nullable()->after('body');
            $table->text('body')->nullable()->change(); // Rendre le body nullable pour les messages de type fichier pur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['type', 'file_path']);
            $table->text('body')->nullable(false)->change();
        });
    }
};
