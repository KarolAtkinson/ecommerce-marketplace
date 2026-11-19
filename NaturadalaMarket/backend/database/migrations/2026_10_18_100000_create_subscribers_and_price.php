<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 5)->default('pl');
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->timestamp('announced_at')->nullable()->after('sort_order');
        });

        // Products that already exist must not trigger a "new product" email.
        DB::table('products')->update(['announced_at' => now()]);

        DB::table('products')
            ->where('sku', 'NAT-COLLAGEN-320')
            ->whereNull('retail_price')
            ->update(['retail_price' => 21.99]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('announced_at');
        });
        Schema::dropIfExists('subscribers');
    }
};
