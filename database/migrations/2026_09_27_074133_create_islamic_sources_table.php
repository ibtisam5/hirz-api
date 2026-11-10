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
    Schema::create('islamic_sources', function (Blueprint $table) {
        $table->id();

        $table->string('type');
        $table->text('text');

        $table->string('source_name');
        $table->string('reference')->nullable();

        $table->string('topic')->nullable();
        $table->json('tags')->nullable();

        $table->text('context_note')->nullable();

        $table->boolean('is_verified')->default(false);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('islamic_sources');
    }
};
