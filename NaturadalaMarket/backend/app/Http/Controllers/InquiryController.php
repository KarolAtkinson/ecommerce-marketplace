<?php

namespace App\Http\Controllers;

use App\Mail\NewInquiryNotification;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function create(Request $request): View
    {
        $user = $request->user();

        return view('contact', [
            'initialProducts' => $request->session()->get('inquiry_products', []),
            'contactDefaults' => [
                'name' => $user?->name,
                'email' => $user?->email,
                'phone' => $user?->phone,
                'company_name' => $user?->retailerProfile?->legal_business_name,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:2000'],
            'products' => ['nullable', 'json', 'max:5000'],
            'product_details' => ['nullable', 'string', 'max:2000'],
        ]);

        $products = isset($validated['products'])
            ? json_decode($validated['products'], true)
            : [];

        if (! is_array($products)) {
            throw ValidationException::withMessages([
                'products' => 'Lista produktów ma nieprawidłowy format.',
            ]);
        }

        $products = Validator::make(
            ['products' => $products],
            [
                'products' => ['array', 'max:20'],
                'products.*.name' => ['required', 'string', 'max:190'],
                'products.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            ],
        )->validate()['products'];

        $message = trim(implode("\n\n", array_filter([
            $validated['message'] ?? null,
            $validated['product_details'] ?? null,
        ])));

        $inquiry = DB::transaction(function () use ($validated, $products, $message): Inquiry {
            return Inquiry::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'message' => $message !== '' ? $message : null,
                'products' => $products,
                'status' => 'new',
            ]);
        });

        $notificationEmail = config('marketplace.inquiry_notification_email');
        if (is_string($notificationEmail) && trim($notificationEmail) !== '') {
            Mail::to(trim($notificationEmail))->send(new NewInquiryNotification($inquiry));
        }

        return redirect()->route('contact')->with('status', 'inquiry-sent');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:new,contacted,closed'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $search = trim($filters['q'] ?? '');
        $inquiries = Inquiry::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'filters' => $filters,
        ]);
    }

    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,closed'],
            'filter_status' => ['nullable', 'in:new,contacted,closed'],
            'filter_q' => ['nullable', 'string', 'max:120'],
        ]);

        $inquiry->update(['status' => $validated['status']]);

        return redirect()->route('admin.inquiries.index', array_filter([
            'status' => $validated['filter_status'] ?? null,
            'q' => $validated['filter_q'] ?? null,
        ], fn (?string $value): bool => $value !== null && $value !== ''))
            ->with('status', 'inquiry-updated');
    }
}
