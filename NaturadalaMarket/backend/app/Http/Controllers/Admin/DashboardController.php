<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\RetailerProfile;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $productsCount = Product::query()->count();
        $activeProductsCount = Product::query()->where('active', true)->count();
        $retailersCount = User::query()->whereHas('roles', fn ($query) => $query->where('slug', 'retailer'))->count();
        $pendingApplicationsCount = RetailerProfile::query()->where('verification_status', 'pending')->count();
        $approvedApplicationsCount = RetailerProfile::query()->where('verification_status', 'approved')->count();
        $newInquiriesCount = Inquiry::query()->where('status', 'new')->count();
        $recentInquiries = Inquiry::query()
            ->where('status', 'new')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'productsCount',
            'activeProductsCount',
            'retailersCount',
            'pendingApplicationsCount',
            'approvedApplicationsCount',
            'newInquiriesCount',
            'recentInquiries',
        ));
    }
}
