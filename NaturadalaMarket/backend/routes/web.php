<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\AddressSuggestionController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityEventController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\RetailerContractController;
use App\Http\Controllers\Admin\RetailerDirectoryController;
use App\Http\Controllers\Admin\RetailerReviewController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BasketController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RetailerApplicationController;
use App\Http\Controllers\RetailerWorkspaceController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WishlistController;
use App\Models\Product;
use App\Http\Middleware\EnsureAccountIsActive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('storefront', [
    'featuredProduct' => Product::query()
        ->available()
        ->where('slug', 'koncentrat-kolagenowy')
        ->first(),
]))->name('home');
Route::post('/language', [LanguageController::class, 'update'])->name('language.update');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/kontakt', [InquiryController::class, 'create'])->name('contact');
Route::post('/newsletter', [SubscriptionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('subscription.store');
Route::get('/newsletter/confirm/{subscriber}', [SubscriptionController::class, 'confirm'])
    ->middleware(['signed', 'throttle:20,1'])
    ->name('subscription.confirm');
Route::get('/newsletter/unsubscribe/{subscriber}', [SubscriptionController::class, 'unsubscribePage'])
    ->middleware(['signed', 'throttle:20,1'])
    ->name('subscription.unsubscribe');
Route::post('/newsletter/unsubscribe/{subscriber}', [SubscriptionController::class, 'unsubscribe'])
    ->middleware(['signed', 'throttle:20,1']);
Route::post('/kontakt', [InquiryController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
Route::get('/terms', function () {
    abort_unless(is_file(resource_path('views/legal/terms.blade.php')), 404);

    return view('legal.terms');
})->name('legal.terms');
Route::get('/privacy', function () {
    abort_unless(is_file(resource_path('views/legal/privacy.blade.php')), 404);

    return view('legal.privacy');
})->name('legal.privacy');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.store');
});

Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])
    ->middleware('throttle:10,1')
    ->name('two-factor.login');
Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('two-factor.login.store');

Route::middleware(['auth', EnsureAccountIsActive::class])->group(function () {
    Route::get('/verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/account', function (Request $request) {
        return view('account.show', ['user' => $request->user()->load('roles', 'retailerProfile')]);
    })->middleware('verified')->name('account.show');

    Route::middleware('verified')->group(function () {
        Route::get('/retailer/workspace', [RetailerWorkspaceController::class, 'index'])
            ->middleware(['retailer.maintenance', 'retailer.approved'])
            ->name('retailer.workspace');
        Route::get('/account/profile', [ProfileController::class, 'edit'])->name('account.profile.edit');
        Route::put('/account/profile', [ProfileController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('account.profile.update');
        Route::put('/account/profile/email', [ProfileController::class, 'updateEmail'])
            ->middleware('throttle:5,1')
            ->name('account.profile.email.update');
        Route::get('/account/security-events', [SecurityEventController::class, 'index'])
            ->name('account.security-events.index');
        Route::post('/account/security-events/revoke-other-sessions', [SecurityEventController::class, 'revokeOtherSessions'])
            ->middleware('throttle:5,1')
            ->name('account.security-events.revoke-other-sessions');
        Route::get('/account/password', [PasswordController::class, 'edit'])->name('account.password.edit');
        Route::put('/account/password', [PasswordController::class, 'update'])
            ->middleware('throttle:5,1')
            ->name('account.password.update');

        Route::get('/account/address-suggestions', AddressSuggestionController::class)
            ->middleware('throttle:30,1')
            ->name('addresses.suggest');

        Route::resource('account/addresses', AddressController::class)
            ->except(['show'])
            ->parameters(['addresses' => 'address'])
            ->names('addresses');

        Route::get('/account/two-factor', [TwoFactorAuthenticationController::class, 'create'])
            ->name('mfa.show');
        Route::post('/account/two-factor', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('mfa.store');
        Route::post('/account/two-factor/recovery-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])
            ->middleware('throttle:5,1')
            ->name('mfa.recovery-codes.regenerate');
        Route::delete('/account/two-factor', [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware('throttle:5,1')
            ->name('mfa.destroy');

        Route::get('/account/retailer-application', [RetailerApplicationController::class, 'create'])
            ->middleware('retailer.maintenance')
            ->name('retailer.application');
        Route::post('/account/retailer-application', [RetailerApplicationController::class, 'store'])
            ->middleware(['retailer.maintenance', 'throttle:5,1'])
            ->name('retailer.application.store');

        Route::get('/account/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/account/wishlist/{product}', [WishlistController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('wishlist.store');
        Route::delete('/account/wishlist/{product}', [WishlistController::class, 'destroy'])
            ->name('wishlist.destroy');

        Route::get('/account/basket', [BasketController::class, 'index'])->name('basket.show');
        Route::post('/account/basket/inquiry', [BasketController::class, 'inquiry'])
            ->middleware('throttle:10,1')
            ->name('basket.inquiry');
        Route::post('/account/basket/{product}', [BasketController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('basket.store');
        Route::patch('/account/basket/items/{item}', [BasketController::class, 'update'])
            ->name('basket.update');
        Route::delete('/account/basket/items/{item}', [BasketController::class, 'destroy'])
            ->name('basket.destroy');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['verified', 'admin.mfa'])
        ->group(function (): void {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::resource('products', ProductManagementController::class)->except(['show']);
            Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
            Route::patch('/inquiries/{inquiry}', [InquiryController::class, 'updateStatus'])
                ->middleware('throttle:20,1')
                ->name('inquiries.update');

            Route::get('/retailers', [RetailerReviewController::class, 'index'])->name('retailers.index');
            Route::patch('/retailers/{profile}', [RetailerReviewController::class, 'update'])
                ->middleware('throttle:20,1')
                ->name('retailers.update');
            Route::get('/retailer-directory', [RetailerDirectoryController::class, 'index'])->name('retailers.directory');
            Route::patch('/retailer-directory/{profile}', [RetailerDirectoryController::class, 'updateAccess'])
                ->middleware('throttle:20,1')
                ->name('retailers.access.update');
            Route::get('/retailer-contracts', [RetailerContractController::class, 'index'])->name('retailers.contracts.index');
            Route::post('/retailer-contracts', [RetailerContractController::class, 'store'])
                ->middleware('throttle:20,1')
                ->name('retailers.contracts.store');
            Route::patch('/retailer-contracts/{contract}', [RetailerContractController::class, 'update'])
                ->middleware('throttle:20,1')
                ->name('retailers.contracts.update');
            Route::delete('/retailer-contracts/{contract}', [RetailerContractController::class, 'destroy'])
                ->middleware('throttle:20,1')
                ->name('retailers.contracts.destroy');
        });
});
