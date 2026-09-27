<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('age')->default(25);
            $table->string('gender')->default('woman'); // man, woman, non-binary
            $table->string('interested_in')->default('all'); // men, women, all
            $table->string('occupation')->nullable();
            $table->string('city')->default('New York, NY');
            $table->integer('distance_km')->default(5);
            $table->text('bio')->nullable();
            $table->string('avatar_url')->nullable();
            $table->json('photos')->nullable();
            $table->json('interests')->nullable();
            $table->timestamps();
        });

        Schema::create('swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('swiper_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('swiped_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['like', 'pass', 'superlike'])->default('like');
            $table->timestamps();

            $table->unique(['swiper_id', 'swiped_id']);
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_two_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('matched_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_one_id', 'user_two_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('swipes');
        Schema::dropIfExists('profiles');
    }
};
