<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lendit_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('lendit_taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('lendit_tags')->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->timestamps();

            $table->unique(['tag_id', 'item_type', 'item_id'], 'lendit_taggables_unique');
            $table->index(['item_type', 'item_id'], 'lendit_taggables_item_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lendit_taggables');
        Schema::dropIfExists('lendit_tags');
    }
};
