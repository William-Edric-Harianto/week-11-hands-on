<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_index_displays_empty_state_when_no_customers_exist(): void
    {
        $response = $this->get(route('customer-list'));

        $response->assertOk();
        $response->assertViewIs('customers.view-customer');
        $response->assertSee('No customers found.');
    }

    public function test_customer_index_displays_customers_with_update_and_delete_actions(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => 'Budi Santoso',
            'phone_number' => '081234567890',
        ]);

        $response = $this->actingAs($user)->get(route('customer-list'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('081234567890');
        $response->assertSee(route('edit-customer', $customer), false);
        $response->assertSee(route('delete-customer', $customer), false);
        $response->assertSee('Update');
        $response->assertSee('Delete');
    }

    public function test_customer_create_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('create-customer'));

        $response->assertOk();
        $response->assertViewIs('customers.create-customer');
        $response->assertSee('Create Customer');
    }

    public function test_customer_can_be_stored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('store-customer'), [
            'name' => 'Siti Aminah',
            'phone_number' => '081987654321',
        ]);

        $response->assertRedirect(route('customer-list'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Siti Aminah',
            'phone_number' => '081987654321',
        ]);
    }

    public function test_customer_edit_page_renders_with_bound_customer(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => 'Andi Wijaya',
            'phone_number' => '085555666777',
        ]);

        $response = $this->actingAs($user)->get(route('edit-customer', $customer));

        $response->assertOk();
        $response->assertViewIs('customers.edit-customer');
        $response->assertSee('Andi Wijaya');
        $response->assertSee('085555666777');
    }

    public function test_customer_can_be_updated(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => 'Old Name',
            'phone_number' => '081111111111',
        ]);

        $response = $this->actingAs($user)->post(route('update-customer', $customer), [
            'name' => 'New Name',
            'phone_number' => '082222222222',
        ]);

        $response->assertRedirect(route('customer-list'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'New Name',
            'phone_number' => '082222222222',
        ]);
    }

    public function test_customer_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->delete(route('delete-customer', $customer));

        $response->assertRedirect(route('customer-list'));
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_store_validates_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('create-customer'))->post(route('store-customer'), [
            'name' => 'A',
            'phone_number' => '12345',
        ]);

        $response->assertRedirect(route('create-customer'));
        $response->assertSessionHasErrors(['name', 'phone_number']);
        $this->assertDatabaseCount('customers', 0);

        $emptyResponse = $this->actingAs($user)->from(route('create-customer'))->post(route('store-customer'), [
            'name' => '',
            'phone_number' => '',
        ]);

        $emptyResponse->assertRedirect(route('create-customer'));
        $emptyResponse->assertSessionHasErrors(['name', 'phone_number']);

        $tooLongResponse = $this->actingAs($user)->from(route('create-customer'))->post(route('store-customer'), [
            'name' => str_repeat('a', 101),
            'phone_number' => str_repeat('1', 21),
        ]);

        $tooLongResponse->assertRedirect(route('create-customer'));
        $tooLongResponse->assertSessionHasErrors(['name', 'phone_number']);
    }

    public function test_customer_update_validates_input(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => 'Original Name',
            'phone_number' => '081234567890',
        ]);

        $response = $this->actingAs($user)->from(route('edit-customer', $customer))->post(route('update-customer', $customer), [
            'name' => 'A',
            'phone_number' => '12345',
        ]);

        $response->assertRedirect(route('edit-customer', $customer));
        $response->assertSessionHasErrors(['name', 'phone_number']);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Original Name',
            'phone_number' => '081234567890',
        ]);
    }

    public function test_customer_profile_picture_can_be_stored_updated_and_displayed(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $profilePicture = UploadedFile::fake()->image('avatar.jpg');

        $storeResponse = $this->actingAs($user)->post(route('store-customer'), [
            'name' => 'Dewi Lestari',
            'phone_number' => '081234567890',
            'profile_picture' => $profilePicture,
        ]);

        $storeResponse->assertRedirect(route('customer-list'));
        $customer = Customer::firstOrFail();
        $this->assertNotNull($customer->profile_picture);
        Storage::disk('public')->assertExists($customer->profile_picture);

        $indexResponse = $this->actingAs($user)->get(route('customer-list'));
        $indexResponse->assertOk();
        $indexResponse->assertSee(asset('storage/'.$customer->profile_picture), false);

        $newPicture = UploadedFile::fake()->image('avatar-new.jpg');
        $oldPicturePath = $customer->profile_picture;

        $updateResponse = $this->actingAs($user)->post(route('update-customer', $customer), [
            'name' => 'Dewi Lestari Updated',
            'phone_number' => '081234567891',
            'profile_picture' => $newPicture,
        ]);

        $updateResponse->assertRedirect(route('customer-list'));
        $customer->refresh();
        Storage::disk('public')->assertMissing($oldPicturePath);
        Storage::disk('public')->assertExists($customer->profile_picture);

        $this->actingAs($user)->delete(route('delete-customer', $customer));
        Storage::disk('public')->assertMissing($customer->profile_picture);
    }
}
