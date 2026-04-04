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
        Schema::table('users', function (Blueprint $user) {
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $user->boolean('must_change_password')->default(false);
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $user) {
            $user->dropColumn('must_change_password');
        });
    }
};
