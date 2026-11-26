<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertViewIs('auth.login');
        $response->assertSee('Login');
    }

    public function test_guest_can_view_customer_and_service_lists_without_crud_actions(): void
    {
        $customer = Customer::factory()->create(['name' => 'Public Customer']);
        $service = Service::factory()->create(['name' => 'Public Service']);

        $customerResponse = $this->get(route('customer-list'));
        $customerResponse->assertOk();
        $customerResponse->assertSee('Public Customer');
        $customerResponse->assertDontSee(route('create-customer'), false);
        $customerResponse->assertDontSee(route('edit-customer', $customer), false);
        $customerResponse->assertDontSee(route('delete-customer', $customer), false);

        $serviceResponse = $this->get(route('service-list'));
        $serviceResponse->assertOk();
        $serviceResponse->assertSee('Public Service');
        $serviceResponse->assertDontSee(route('create-service'), false);
        $serviceResponse->assertDontSee(route('edit-service', $service), false);
        $serviceResponse->assertDontSee(route('delete-service', $service), false);
    }

    public function test_guest_is_redirected_to_login_for_customer_and_service_crud_routes(): void
    {
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        $this->get(route('create-customer'))->assertRedirect(route('login'));
        $this->post(route('store-customer'), [
            'name' => 'Blocked Customer',
            'phone_number' => '081234567890',
        ])->assertRedirect(route('login'));
        $this->get(route('edit-customer', $customer))->assertRedirect(route('login'));
        $this->post(route('update-customer', $customer), [
            'name' => 'Blocked Update',
            'phone_number' => '081234567890',
        ])->assertRedirect(route('login'));
        $this->delete(route('delete-customer', $customer))->assertRedirect(route('login'));

        $this->get(route('create-service'))->assertRedirect(route('login'));
        $this->post(route('store-service'), [
            'name' => 'Blocked Service',
            'description' => 'Should not store',
        ])->assertRedirect(route('login'));
        $this->get(route('edit-service', $service))->assertRedirect(route('login'));
        $this->post(route('update-service', $service), [
            'name' => 'Blocked Update',
            'description' => 'Should not update',
        ])->assertRedirect(route('login'));
        $this->delete(route('delete-service', $service))->assertRedirect(route('login'));

        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('services', 1);
    }

    public function test_user_can_login_with_valid_credentials_and_redirect_to_intended_url(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => Hash::make('demo1234'),
        ]);

        $this->get(route('create-customer'))->assertRedirect(route('login'));

        $response = $this->post(route('login'), [
            'email' => 'student@example.com',
            'password' => 'demo1234',
        ]);

        $response->assertRedirect(route('create-customer'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'student@example.com',
            'password' => Hash::make('demo1234'),
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'student@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_for_admin_routes(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('login'));
        $this->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'user',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_non_admin_user_receives_forbidden_on_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.users.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauth@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'admin',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'unauth@example.com']);
    }

    public function test_admin_can_access_admin_page_and_create_new_users(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Super Admin',
        ]);

        $pageResponse = $this->actingAs($admin)->get(route('admin.index'));
        $pageResponse->assertOk();
        $pageResponse->assertViewIs('admin.index');
        $pageResponse->assertSee('Welcome, Super Admin');

        $createResponse = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Fresh Member',
            'email' => 'member@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'user',
        ]);

        $createResponse->assertRedirect(route('admin.index'));
        $createResponse->assertSessionHas('status', 'User created successfully.');

        $createdUser = User::where('email', 'member@example.com')->firstOrFail();
        $this->assertSame('Fresh Member', $createdUser->name);
        $this->assertSame('user', $createdUser->role);
        $this->assertTrue(Hash::check('secret123', $createdUser->password));
    }

    public function test_admin_user_creation_validates_input(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.index'))
            ->post(route('admin.users.store'), [
                'name' => 'A',
                'email' => 'admin@example.com',
                'password' => '123',
                'password_confirmation' => '456',
                'role' => 'superadmin',
            ]);

        $response->assertRedirect(route('admin.index'));
        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
        $this->assertDatabaseCount('users', 1);
    }
}
