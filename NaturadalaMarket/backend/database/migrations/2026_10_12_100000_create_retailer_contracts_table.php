<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retailer_contracts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('retailer_profile_id')->constrained()->restrictOnDelete();
            $table->string('reference', 64);
            $table->string('title', 190);
            $table->string('contract_type', 100);
            $table->foreignId('internal_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->unsignedSmallInteger('notice_period_days')->nullable();
            $table->timestamps();
            $table->unique(['retailer_profile_id', 'reference']);
            $table->index(['retailer_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retailer_contracts');
    }
};
