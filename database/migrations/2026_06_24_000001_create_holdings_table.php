<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('holdings.holdings_table', 'holdings'), function (Blueprint $table) {
            $table->id();
            $table->morphs('owner');

            $table->string('name');
            $table->string('symbol')->nullable();
            $table->decimal('quantity', 24, 8)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->decimal('unit_price', 18, 4)->default(0);

            $table->string('price_source')->default('manual');
            $table->string('provider')->nullable();
            $table->string('external_ref')->nullable();
            $table->datetime('last_priced_at')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['provider', 'external_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('holdings.holdings_table', 'holdings'));
    }
};
