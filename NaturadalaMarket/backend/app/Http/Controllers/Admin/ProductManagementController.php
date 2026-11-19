<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WholesalePrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'active' => ['nullable', 'in:0,1'],
        ]);

        $search = trim($filters['q'] ?? '');
        $products = Product::query()
            ->with('wholesalePrices')
            ->withCount(['basketItems', 'wishlistItems'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when(isset($filters['active']), fn (Builder $query) => $query->where('active', $filters['active'] === '1'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'filters'));
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product,
            'wholesalePrices' => [
                ['id' => null, 'minimum_quantity' => 10, 'unit_price' => null, 'starts_at' => '', 'ends_at' => ''],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request, null);

        DB::transaction(function () use ($validated): void {
            $product = Product::query()->create($validated['product']);
            $this->syncWholesalePrices($product, $validated['wholesale_prices'] ?? []);
        });

        return redirect()->route('admin.products.index')->with('status', 'product-saved');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'wholesalePrices' => $product->wholesalePrices()->orderBy('minimum_quantity')->get()->map(fn (WholesalePrice $price) => [
                'id' => $price->id,
                'minimum_quantity' => $price->minimum_quantity,
                'unit_price' => $price->unit_price,
                'starts_at' => $price->starts_at?->format('Y-m-d'),
                'ends_at' => $price->ends_at?->format('Y-m-d'),
            ])->all(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);

        DB::transaction(function () use ($product, $validated): void {
            $product->update($validated['product']);
            $this->syncWholesalePrices($product, $validated['wholesale_prices'] ?? []);
        });

        return redirect()->route('admin.products.index')->with('status', 'product-saved');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $deleted = DB::transaction(function () use ($product): bool {
            $product = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($product->basketItems()->exists() || $product->wishlistItems()->exists()) {
                return false;
            }

            $product->delete();

            return true;
        });

        return redirect()->route('admin.products.index')->with(
            'status',
            $deleted ? 'product-deleted' : 'product-in-use'
        );
    }

    /**
     * @return array{product: array<string, mixed>, wholesale_prices: array<int, array<string, mixed>>}
     */
    private function validateProduct(Request $request, ?Product $product): array
    {
        $validator = Validator::make($request->all(), [
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($product?->id)],
            'slug' => ['required', 'string', 'max:190', Rule::unique('products', 'slug')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:190'],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'size_label' => ['nullable', 'string', 'max:80'],
            'image_url' => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/\S+|\/[^\s]+)$/'],
            'retail_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'minimum_order_quantity' => ['required', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'wholesale_prices' => ['nullable', 'array'],
            'wholesale_prices.*.id' => [
                'nullable',
                'integer',
                Rule::exists('wholesale_prices', 'id')->where('product_id', $product?->id ?? 0),
                'distinct:strict',
            ],
            'wholesale_prices.*.minimum_quantity' => [
                'nullable',
                'integer',
            ],
            'wholesale_prices.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'wholesale_prices.*.starts_at' => ['nullable', 'date'],
            'wholesale_prices.*.ends_at' => ['nullable', 'date'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $minimumOrderQuantity = filter_var(
                $request->input('minimum_order_quantity'),
                FILTER_VALIDATE_INT
            );
            $minimumOrderQuantity = $minimumOrderQuantity === false ? 1 : $minimumOrderQuantity;
            $thresholds = [];

            foreach ($request->input('wholesale_prices', []) as $index => $tier) {
                if (! is_array($tier) || trim((string) ($tier['unit_price'] ?? '')) === '') {
                    continue;
                }

                $startsAtKey = "wholesale_prices.{$index}.starts_at";
                $endsAtKey = "wholesale_prices.{$index}.ends_at";
                if (
                    isset($tier['starts_at'], $tier['ends_at'])
                    && $tier['starts_at'] !== ''
                    && $tier['ends_at'] !== ''
                    && ! $validator->errors()->has($startsAtKey)
                    && ! $validator->errors()->has($endsAtKey)
                    && Carbon::parse($tier['ends_at'])->lt(Carbon::parse($tier['starts_at']))
                ) {
                    $validator->errors()->add($endsAtKey, 'Data końcowa musi być równa lub późniejsza niż data początkowa.');
                }

                $minimumQuantityKey = "wholesale_prices.{$index}.minimum_quantity";
                if ($validator->errors()->has($minimumQuantityKey)) {
                    continue;
                }

                $minimumQuantityValue = $tier['minimum_quantity'] ?? null;
                if ($minimumQuantityValue === null || trim((string) $minimumQuantityValue) === '') {
                    $validator->errors()->add($minimumQuantityKey, 'Wymagana jest minimalna ilość dla wycenionego progu.');

                    continue;
                }

                $minimumQuantity = filter_var($minimumQuantityValue, FILTER_VALIDATE_INT);
                if ($minimumQuantity === false) {
                    continue;
                }

                if ($minimumQuantity < $minimumOrderQuantity) {
                    $validator->errors()->add(
                        $minimumQuantityKey,
                        "Ilość musi wynosić co najmniej {$minimumOrderQuantity}."
                    );
                }

                if (in_array($minimumQuantity, $thresholds, true)) {
                    $validator->errors()->add(
                        $minimumQuantityKey,
                        'Każdy próg cenowy musi mieć inną minimalną ilość.'
                    );
                }

                $thresholds[] = $minimumQuantity;
            }
        });

        $validated = $validator->validate();

        $productData = [
            'sku' => $validated['sku'],
            'slug' => $validated['slug'],
            'name' => $validated['name'],
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'size_label' => $validated['size_label'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'retail_price' => $validated['retail_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? null,
            'minimum_order_quantity' => $validated['minimum_order_quantity'],
            'active' => (bool) ($validated['active'] ?? false),
            'sort_order' => $validated['sort_order'],
        ];

        return [
            'product' => $productData,
            'wholesale_prices' => $validated['wholesale_prices'] ?? [],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncWholesalePrices(Product $product, array $rows): void
    {
        $activeIds = [];

        foreach ($rows as $row) {
            $unitPrice = $row['unit_price'] ?? null;
            if ($unitPrice === null || trim((string) $unitPrice) === '') {
                continue;
            }

            $priceData = [
                'product_id' => $product->id,
                'unit_price' => $unitPrice,
                'minimum_quantity' => (int) ($row['minimum_quantity'] ?? 1),
                'starts_at' => filled($row['starts_at'] ?? null)
                    ? Carbon::parse($row['starts_at'])->startOfDay()
                    : null,
                'ends_at' => filled($row['ends_at'] ?? null)
                    ? Carbon::parse($row['ends_at'])->endOfDay()
                    : null,
            ];

            if (! empty($row['id'])) {
                /** @var \App\Models\WholesalePrice|null $price */
                $price = $product->wholesalePrices()->whereKey($row['id'])->first();
                if ($price !== null) {
                    $price->update($priceData);
                    $activeIds[] = $price->id;

                    continue;
                }
            }

            $activeIds[] = $product->wholesalePrices()->create($priceData)->id;
        }

        if ($activeIds === []) {
            $product->wholesalePrices()->delete();

            return;
        }

        $product->wholesalePrices()->whereNotIn('id', $activeIds)->delete();
    }
}
