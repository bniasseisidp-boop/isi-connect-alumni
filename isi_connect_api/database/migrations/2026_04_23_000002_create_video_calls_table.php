<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('callee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('work_group_id')->nullable()->constrained('work_groups')->cascadeOnDelete();
            $table->string('room_id')->unique();
            $table->enum('status', ['waiting', 'active', 'ended', 'rejected'])->default('waiting');
            $table->text('offer')->nullable();
            $table->text('answer')->nullable();
            $table->timestamps();
        });

        Schema::create('call_ice_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('video_calls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('candidate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_ice_candidates');
        Schema::dropIfExists('video_calls');
    }
};
