<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketplaceAddressBookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function addressData(array $overrides = []): array
    {
        return [
            'label' => 'Dom',
            'recipient_name' => 'Jan Kowalski',
            'company_name' => 'Naturadała Partner',
            'street_line_1' => 'Długa 1',
            'street_line_2' => '',
            'postal_code' => '00-001',
            'city' => 'Warszawa',
            'region' => 'Mazowieckie',
            'country_code' => 'pl',
            'phone' => '+48123456789',
            'is_default_billing' => '1',
            'is_default_shipping' => '1',
            ...$overrides,
        ];
    }

    public function test_address_suggestions_are_normalised_from_the_lookup_service(): void
    {
        Http::fake([
            '*' => Http::response([
                ['address' => ['road' => 'Długa', 'house_number' => '1', 'postcode' => '00-001', 'city' => 'Warszawa', 'state' => 'województwo mazowieckie', 'country_code' => 'pl']],
                ['address' => ['postcode' => '00-002']],
            ]),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('addresses.suggest', ['q' => 'Długa 1 Warszawa']))
            ->assertOk()
            ->assertExactJson(['suggestions' => [[
                'street' => 'Długa 1',
                'postal_code' => '00-001',
                'city' => 'Warszawa',
                'region' => 'mazowieckie',
                'country_code' => 'PL',
            ]]]);

        $this->getJson(route('addresses.suggest', ['q' => 'ab']))->assertUnprocessable();
    }

    public function test_verified_customer_can_create_edit_and_delete_an_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('addresses.index'))
            ->assertOk()
            ->assertSee('Nie masz jeszcze zapisanych adresów.');

        $this->post(route('addresses.store'), $this->addressData())
            ->assertRedirect(route('addresses.index'))
            ->assertSessionHas('status', 'address-saved');

        $userId = (int) $user->getKey();
        $address = Address::query()->where('user_id', $userId)->firstOrFail();
        $addressId = (int) $address->getKey();
        $this->assertSame('PL', $address->country_code);
        $this->assertTrue($address->is_default_billing);
        $this->assertTrue($address->is_default_shipping);

        $this->get(route('addresses.index'))
            ->assertOk()
            ->assertSee('Dom')
            ->assertSee('Domyślny rozliczeniowy')
            ->assertSee('Domyślny dostawy');

        $this->put(route('addresses.update', $address), $this->addressData([
            'label' => 'Biuro',
            'country_code' => 'us',
            'is_default_billing' => '0',
            'is_default_shipping' => '0',
        ]))
            ->assertRedirect(route('addresses.index'))
            ->assertSessionHas('status', 'address-saved');

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'user_id' => $userId,
            'label' => 'Biuro',
            'country_code' => 'US',
            'is_default_billing' => false,
            'is_default_shipping' => false,
        ]);

        $this->delete(route('addresses.destroy', $address))
            ->assertRedirect(route('addresses.index'))
            ->assertSessionHas('status', 'address-deleted');

        $this->assertDatabaseMissing('addresses', ['id' => $addressId]);
    }

    public function test_default_billing_and_shipping_addresses_are_unique_independently(): void
    {
        $user = User::factory()->create();
        $first = $user->addresses()->create($this->addressData());
        $second = $user->addresses()->create($this->addressData([
            'label' => 'Drugi adres',
            'street_line_1' => 'Krótka 2',
            'is_default_billing' => false,
            'is_default_shipping' => false,
        ]));

        $this->actingAs($user)
            ->put(route('addresses.update', $second), $this->addressData([
                'label' => 'Nowy domyślny',
                'street_line_1' => 'Krótka 2',
                'is_default_billing' => '1',
                'is_default_shipping' => '0',
            ]))
            ->assertRedirect(route('addresses.index'));

        $this->assertFalse($first->fresh()->is_default_billing);
        $this->assertTrue($second->fresh()->is_default_billing);
        $this->assertTrue($first->fresh()->is_default_shipping);
        $this->assertFalse($second->fresh()->is_default_shipping);
    }

    public function test_customer_cannot_view_or_modify_another_users_address(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $address = $owner->addresses()->create($this->addressData());
        $addressId = (int) $address->getKey();

        $this->actingAs($otherUser)
            ->get(route('addresses.edit', $address))
            ->assertNotFound();

        $this->put(route('addresses.update', $address), $this->addressData([
            'label' => 'Nieautoryzowana zmiana',
        ]))->assertNotFound();

        $this->delete(route('addresses.destroy', $address))->assertNotFound();

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'user_id' => (int) $owner->getKey(),
            'label' => 'Dom',
        ]);
    }

    public function test_unverified_customer_cannot_access_the_address_book(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('addresses.index'))
            ->assertRedirect(route('verification.notice'));

        $this->post(route('addresses.store'), $this->addressData())
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_address_requires_valid_recipient_and_country_code(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('addresses.create'))
            ->post(route('addresses.store'), $this->addressData([
                'recipient_name' => '',
                'country_code' => '1!',
            ]))
            ->assertRedirect(route('addresses.create'))
            ->assertSessionHasErrors(['recipient_name', 'country_code']);

        $this->assertDatabaseCount('addresses', 0);
    }
}
