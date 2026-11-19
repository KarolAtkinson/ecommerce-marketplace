<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetailerWorkspaceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $search = trim($filters['q'] ?? '');
        $products = Product::query()
            ->available()
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            }))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $products->getCollection()->load(['wholesalePrices' => function (Builder|Relation $query): void {
            $query
                ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->orderBy('minimum_quantity');
        }]);

        $profile = $request->user()->retailerProfile()
            ->with([
                'contracts' => fn ($query) => $query->whereIn('status', ['signed', 'active', 'expired', 'terminated']),
                'contracts.internalOwner',
            ])
            ->firstOrFail();

        return view('retailer.workspace', [
            'products' => $products,
            'profile' => $profile,
            'search' => $search,
        ]);
    }
}
