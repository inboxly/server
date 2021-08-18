<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOriginalFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('original_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('fetcher_key');
            $table->string('fetcher_feed_id');
            $table->json('parameters');
            $table->string('name');
            $table->text('summary')->nullable();
            $table->string('url')->nullable();
            $table->string('image')->nullable();
            $table->string('author')->nullable();
            $table->string('language')->nullable();
            $table->timestamp('next_update_at', 6)->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('original_feeds');
    }
}
