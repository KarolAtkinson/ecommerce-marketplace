<?php

namespace Tests\Feature;

use App\Mail\NewInquiryNotification;
use App\Models\Inquiry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_can_store_a_quote_request_and_admin_can_update_its_status(): void
    {
        Mail::fake();
        config(['marketplace.inquiry_notification_email' => 'sales@example.test']);

        $this->post(route('contact.store'), [
            'name' => 'Anna Kwiatkowska',
            'email' => 'anna@example.test',
            'phone' => '555111222',
            'company_name' => 'Naturana',
            'products' => json_encode([
                ['name' => 'Koncentrat kolagenowy Naturadała', 'quantity' => 12],
            ]),
            'product_details' => 'Preferowana dostawa w przyszłym miesiącu.',
            'message' => 'Potrzebujemy 12 opakowań do testów.',
        ])->assertRedirect(route('contact'))
            ->assertSessionHas('status', 'inquiry-sent');

        $inquiry = Inquiry::query()->firstOrFail();
        $this->assertSame([
            ['name' => 'Koncentrat kolagenowy Naturadała', 'quantity' => 12],
        ], $inquiry->products);
        $this->assertStringContainsString('Preferowana dostawa w przyszłym miesiącu.', $inquiry->message);

        $this->assertDatabaseHas('inquiries', [
            'email' => 'anna@example.test',
            'company_name' => 'Naturana',
            'status' => 'new',
        ]);

        Mail::assertSent(NewInquiryNotification::class, function (NewInquiryNotification $mail) use ($inquiry): bool {
            return $mail->inquiry->is($inquiry)
                && $mail->hasTo('sales@example.test')
                && str_contains($mail->render(), 'Preferowana dostawa w przyszłym miesiącu.');
        });

        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.inquiries.index'))
            ->assertOk()
            ->assertSee('anna@example.test')
            ->assertSee('Koncentrat kolagenowy Naturadała')
            ->assertSee('Preferowana dostawa w przyszłym miesiącu.')
            ->assertSee('Potrzebujemy 12 opakowań do testów.')
            ->assertSee('Nowe');

        $this->actingAs($admin)
            ->patch(route('admin.inquiries.update', $inquiry), ['status' => 'contacted'])
            ->assertRedirect(route('admin.inquiries.index'));

        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'status' => 'contacted',
        ]);
    }

    public function test_contact_form_rejects_malformed_product_entries(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Anna Kwiatkowska',
                'email' => 'anna@example.test',
                'products' => json_encode([
                    ['name' => 'Koncentrat kolagenowy Naturadała', 'quantity' => 0],
                ]),
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors('products.0.quantity');

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_contact_form_limits_submission_rate(): void
    {
        Mail::fake();
        config(['marketplace.inquiry_notification_email' => null]);

        $payload = [
            'name' => 'Anna Kwiatkowska',
            'email' => 'anna@example.test',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('contact.store'), $payload)
                ->assertRedirect(route('contact'));
        }

        $this->post(route('contact.store'), $payload)
            ->assertStatus(429);

        $this->assertDatabaseCount('inquiries', 5);
        Mail::assertNothingOutgoing();
    }

    public function test_admin_can_filter_inquiries_by_status_and_contact_details(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        Inquiry::query()->create([
            'name' => 'Anna Example',
            'email' => 'anna@shop.example',
            'company_name' => 'Green Shop',
            'status' => 'new',
        ]);
        Inquiry::query()->create([
            'name' => 'Jan Other',
            'email' => 'jan@other.example',
            'company_name' => 'Other Store',
            'status' => 'closed',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.inquiries.index', ['status' => 'new', 'q' => 'Green']))
            ->assertOk()
            ->assertSee('anna@shop.example')
            ->assertDontSee('jan@other.example');

        $this->get(route('admin.inquiries.index', ['q' => 'jan@other']))
            ->assertOk()
            ->assertSee('jan@other.example')
            ->assertDontSee('anna@shop.example');
    }
}
