<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name'); 
            $table->tinyInteger('rating')->unsigned(); 
            $table->text('comment');
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
