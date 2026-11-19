<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $wishlist = $request->user()->wishlist()->with('items.product')->first();

        return view('account.wishlist', [
            'products' => $wishlist?->items->pluck('product') ?? collect(),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->active, 404);

        $wishlist = $request->user()->wishlist()->firstOrCreate();
        $wishlist->items()->firstOrCreate(['product_id' => $product->id]);

        return back()->with('status', 'wishlist-item-added');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlist?->items()->where('product_id', $product->id)->delete();

        return back()->with('status', 'wishlist-item-removed');
    }
}
