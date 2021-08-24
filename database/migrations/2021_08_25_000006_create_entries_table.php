<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('feed_id');
            $table->foreign('feed_id')->references('id')->on('feeds')->cascadeOnDelete();
            $table->string('external_id')->unique();
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
        Schema::dropIfExists('entries');
    }
}
