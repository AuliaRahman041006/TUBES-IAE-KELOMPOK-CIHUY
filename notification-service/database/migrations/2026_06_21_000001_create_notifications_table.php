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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();   // dari Order Service
            $table->string('recipient');                           // nama penerima (user)
            $table->enum('type', ['order_created', 'status_changed', 'order_cancelled', 'general'])->default('general');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();                     // data tambahan dalam format JSON
            $table->timestamp('read_at')->nullable();             // waktu dibaca
            $table->timestamps();

            $table->index('recipient');
            $table->index('order_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
