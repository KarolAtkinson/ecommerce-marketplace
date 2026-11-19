<?php

namespace App\Http\Controllers;

use App\Models\BasketItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BasketController extends Controller
{
    public function index(Request $request): View
    {
        $basket = $request->user()->basket()->with('items.product')->first();

        return view('account.basket', [
            'items' => $basket?->items ?? collect(),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->active, 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        if ($product->stock_quantity !== null && $validated['quantity'] > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Wybrana ilość przekracza aktualnie dostępny stan produktu.',
            ]);
        }

        if ($validated['quantity'] < $product->minimum_order_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Minimalna ilość tego produktu to {$product->minimum_order_quantity}.",
            ]);
        }

        DB::transaction(function () use ($request, $product, $validated): void {
            $basket = $request->user()->basket()->firstOrCreate();
            $item = $basket->items()->where('product_id', $product->id)->lockForUpdate()->first();
            $quantity = $validated['quantity'] + ($item?->quantity ?? 0);

            if ($quantity > 999 || ($product->stock_quantity !== null && $quantity > $product->stock_quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Łączna ilość w koszyku przekracza dostępny limit.',
                ]);
            }

            BasketItem::query()->updateOrCreate(
                ['basket_id' => $basket->id, 'product_id' => $product->id],
                ['quantity' => $quantity]
            );
        });

        return redirect()->route('basket.show')->with('status', 'basket-item-added');
    }

    public function inquiry(Request $request): RedirectResponse
    {
        $items = $request->user()->basket()
            ->first()
            ?->items()
            ->with('product')
            ->whereHas('product', fn ($query) => $query->where('active', true))
            ->get() ?? collect();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'basket' => 'Dodaj aktywne produkty do koszyka, aby wysłać zapytanie.',
            ]);
        }

        $products = $items->map(fn (BasketItem $item): array => [
            'name' => $item->product->name,
            'quantity' => $item->quantity,
        ])->all();

        $request->session()->flash('inquiry_products', $products);

        return redirect()->route('contact');
    }

    public function update(Request $request, BasketItem $item): RedirectResponse
    {
        abort_unless($item->basket->user_id === $request->user()->id, 404);
        abort_unless($item->product->active, 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        if ($item->product->stock_quantity !== null && $validated['quantity'] > $item->product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Wybrana ilość przekracza aktualnie dostępny stan produktu.',
            ]);
        }

        if ($validated['quantity'] < $item->product->minimum_order_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Minimalna ilość tego produktu to {$item->product->minimum_order_quantity}.",
            ]);
        }

        $item->update(['quantity' => $validated['quantity']]);

        return back()->with('status', 'basket-updated');
    }

    public function destroy(Request $request, BasketItem $item): RedirectResponse
    {
        abort_unless($item->basket->user_id === $request->user()->id, 404);
        $item->delete();

        return back()->with('status', 'basket-item-removed');
    }
}
