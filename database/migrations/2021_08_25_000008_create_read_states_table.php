<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('read_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('entry_id');
            $table->foreign('entry_id')->references('id')->on('entries')->cascadeOnDelete();

            $table->string('feed_id');
            $table->foreign('feed_id')->references('id')->on('feeds')->cascadeOnDelete();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('read_states');
    }
}
