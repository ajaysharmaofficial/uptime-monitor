<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('last_status')->default('unknown');
            $table->unsignedSmallInteger('last_status_code')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_alert_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'url']);
            $table->index(['last_status', 'last_checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
