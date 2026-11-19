<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('retail_price', '21.99')
            ->update(['retail_price' => '34.99']);

        DB::table('products')
            ->where('sku', 'NAT-COLLAGEN-320')
            ->update(['name' => 'Naturadała – suplement diety Bulion kolagenowy']);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('retail_price', '34.99')
            ->update(['retail_price' => '21.99']);

        DB::table('products')
            ->where('sku', 'NAT-COLLAGEN-320')
            ->update(['name' => 'Koncentrat kolagenowy Naturadała']);
    }
};
