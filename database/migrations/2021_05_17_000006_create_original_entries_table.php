<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOriginalEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('original_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_feed_id')->constrained();
            $table->string('external_id')->unique();
            $table->string('hash')->unique();
            $table->string('name');
            $table->text('summary')->nullable();
            $table->text('content')->nullable();
            $table->string('url')->nullable();
            $table->string('image')->nullable();
            $table->json('author')->nullable();
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('original_entries');
    }
}
