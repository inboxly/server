<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('fetcher_key');
            $table->string('fetcher_feed_id');
            $table->json('parameters');
            $table->text('summary')->nullable();
            $table->string('url')->nullable();
            $table->string('image')->nullable();
            $table->string('author')->nullable();
            $table->string('language')->nullable();
            $table->timestamp('next_update_at', 6)->nullable();
            $table->timestamps(6);

            // Indexes
            $table->unique(['fetcher_key', 'fetcher_feed_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
}
