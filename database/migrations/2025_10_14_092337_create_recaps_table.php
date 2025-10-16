<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('recap_text'); 
            $table->json('source_journal_ids'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recaps');
    }
};

