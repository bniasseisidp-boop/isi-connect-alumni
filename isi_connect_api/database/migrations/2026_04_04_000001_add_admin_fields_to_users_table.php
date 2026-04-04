<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('role');
            $table->boolean('must_change_password')->default(false)->after('is_blocked');
        });

        // Also add is_featured_in_showcase to profiles if not exists
        if (!Schema::hasColumn('profiles', 'is_featured_in_showcase')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->boolean('is_featured_in_showcase')->default(false)->after('is_visible');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'must_change_password']);
        });
    }
};
