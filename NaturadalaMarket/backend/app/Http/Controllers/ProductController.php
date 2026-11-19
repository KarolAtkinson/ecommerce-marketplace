<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:default,name,newest'],
        ]);

        $search = trim($filters['q'] ?? '');
        $sort = $filters['sort'] ?? 'default';

        $productsQuery = Product::query()
            ->available()
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }));

        $productsQuery->when($sort === 'name', fn (Builder $query) => $query->orderBy('name'))
            ->when($sort === 'newest', fn (Builder $query) => $query->orderByDesc('created_at'))
            ->unless($sort === 'name' || $sort === 'newest', fn (Builder $query) => $query->orderBy('sort_order')->orderBy('name'));

        $products = $productsQuery->paginate(12)->withQueryString();

        if ($this->canSeeWholesalePrices($request)) {
            $products->getCollection()->load(['wholesalePrices' => function (Builder|Relation $query): void {
                $query
                    ->where(function (Builder $query): void {
                        $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                    })
                    ->where(function (Builder $query): void {
                        $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    })
                    ->orderBy('minimum_quantity');
            }]);
        }

        $wishlistedIds = $request->user()
            ? $request->user()->wishlist()->with('items')->first()?->items->pluck('product_id')->all() ?? []
            : [];

        return view('products.index', [
            'products' => $products,
            'approvedRetailer' => $this->canSeeWholesalePrices($request),
            'wishlistedIds' => $wishlistedIds,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->active, 404);

        $descriptionParagraphs = preg_split(
            '/\R\s*\R/u',
            trim($product->description),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [$product->description];

        if (count($descriptionParagraphs) > 1 && str_ends_with(trim($descriptionParagraphs[0]), ':')) {
            $descriptionParagraphs[0] .= ' '.$descriptionParagraphs[1];
            array_splice($descriptionParagraphs, 1, 1);
        }

        if (count($descriptionParagraphs) === 1) {
            $descriptionParagraphs = preg_split(
                '/(?<=[.!?])\s+(?=\p{Lu})/u',
                $descriptionParagraphs[0],
                2,
                PREG_SPLIT_NO_EMPTY
            ) ?: $descriptionParagraphs;
        }

        $previewCount = 1;
        if ($product->sku === 'NAT-COLLAGEN-320'
            && count($descriptionParagraphs) >= 4
            && $descriptionParagraphs[0] === 'Bulion kolagenowy to naturalna baza.'
            && str_starts_with($descriptionParagraphs[1], 'Każdy z nas ma inny gust, smak i własne kulinarne upodobania.')
            && str_starts_with($descriptionParagraphs[2], 'Dodaj ulubione zioła, przyprawy, odrobinę soli lub świeże warzywa.')
            && str_starts_with($descriptionParagraphs[3], 'Nie narzucamy jednej kompozycji przypraw')) {
            $previewCount = 4;
        }

        $descriptionPreview = array_splice($descriptionParagraphs, 0, $previewCount);

        $approvedRetailer = $this->canSeeWholesalePrices($request);
        if ($approvedRetailer) {
            $product->load(['wholesalePrices' => function (Builder|Relation $query): void {
                $query
                    ->where(function (Builder $query): void {
                        $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                    })
                    ->where(function (Builder $query): void {
                        $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    })
                    ->orderBy('minimum_quantity');
            }]);
        }

        $wishlisted = $request->user()
            ? $request->user()->wishlist()->whereHas('items', fn (Builder $query) => $query->where('product_id', $product->id))->exists()
            : false;

        return view('products.show', [
            'product' => $product,
            'descriptionPreview' => $descriptionPreview,
            'descriptionRemainder' => $descriptionParagraphs,
            'approvedRetailer' => $approvedRetailer,
            'wishlisted' => $wishlisted,
        ]);
    }

    private function canSeeWholesalePrices(Request $request): bool
    {
        if (config('features.retailer_maintenance')) {
            return false;
        }

        return $request->user()?->isApprovedRetailer() ?? false;
    }
}
