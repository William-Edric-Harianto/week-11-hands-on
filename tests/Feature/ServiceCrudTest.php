<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_index_displays_empty_state_when_no_services_exist(): void
    {
        $response = $this->get(route('service-list'));

        $response->assertOk();
        $response->assertViewIs('services.view-service');
        $response->assertSee('No services found.');
    }

    public function test_service_index_displays_services_with_update_and_delete_actions(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Deep Cleaning',
            'description' => 'Full interior and exterior cleaning.',
        ]);

        $response = $this->actingAs($user)->get(route('service-list'));

        $response->assertOk();
        $response->assertSee('Deep Cleaning');
        $response->assertSee('Full interior and exterior cleaning.');
        $response->assertSee(route('edit-service', $service), false);
        $response->assertSee(route('delete-service', $service), false);
        $response->assertSee('Update');
        $response->assertSee('Delete');
    }

    public function test_service_create_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('create-service'));

        $response->assertOk();
        $response->assertViewIs('services.create-service');
        $response->assertSee('Create Service');
    }

    public function test_service_can_be_stored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('store-service'), [
            'name' => 'Laptop Repair',
            'description' => 'Hardware diagnostics and replacement.',
        ]);

        $response->assertRedirect(route('service-list'));
        $this->assertDatabaseHas('services', [
            'name' => 'Laptop Repair',
            'description' => 'Hardware diagnostics and replacement.',
        ]);
    }

    public function test_service_edit_page_renders_with_bound_service(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Screen Replacement',
            'description' => 'Replace cracked LCD display.',
        ]);

        $response = $this->actingAs($user)->get(route('edit-service', $service));

        $response->assertOk();
        $response->assertViewIs('services.edit-service');
        $response->assertSee('Screen Replacement');
        $response->assertSee('Replace cracked LCD display.');
    }

    public function test_service_can_be_updated(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Old Service',
            'description' => 'Old description.',
        ]);

        $response = $this->actingAs($user)->post(route('update-service', $service), [
            'name' => 'Updated Service',
            'description' => 'Updated description.',
        ]);

        $response->assertRedirect(route('service-list'));
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated Service',
            'description' => 'Updated description.',
        ]);
    }

    public function test_service_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user)->delete(route('delete-service', $service));

        $response->assertRedirect(route('service-list'));
        $this->assertDatabaseMissing('services', [
            'id' => $service->id,
        ]);
    }

    public function test_service_store_validates_input(): void
    {
        $user = User::factory()->create();

        $emptyResponse = $this->actingAs($user)->from(route('create-service'))->post(route('store-service'), [
            'name' => '',
            'description' => '',
        ]);

        $emptyResponse->assertRedirect(route('create-service'));
        $emptyResponse->assertSessionHasErrors(['name', 'description']);
        $this->assertDatabaseCount('services', 0);

        $tooLongResponse = $this->actingAs($user)->from(route('create-service'))->post(route('store-service'), [
            'name' => str_repeat('a', 101),
            'description' => str_repeat('b', 1001),
        ]);

        $tooLongResponse->assertRedirect(route('create-service'));
        $tooLongResponse->assertSessionHasErrors(['name', 'description']);
        $this->assertDatabaseCount('services', 0);
    }

    public function test_service_update_validates_input(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Original Service',
            'description' => 'Original description.',
        ]);

        $response = $this->actingAs($user)->from(route('edit-service', $service))->post(route('update-service', $service), [
            'name' => '',
            'description' => str_repeat('b', 1001),
        ]);

        $response->assertRedirect(route('edit-service', $service));
        $response->assertSessionHasErrors(['name', 'description']);
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Original Service',
            'description' => 'Original description.',
        ]);
    }

    public function test_service_image_can_be_stored_updated_and_displayed(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $image = UploadedFile::fake()->image('service.jpg');

        $storeResponse = $this->actingAs($user)->post(route('store-service'), [
            'name' => 'AC Cleaning',
            'description' => 'Full AC unit cleaning.',
            'image' => $image,
        ]);

        $storeResponse->assertRedirect(route('service-list'));
        $service = Service::firstOrFail();
        $this->assertNotNull($service->image);
        Storage::disk('public')->assertExists($service->image);

        $indexResponse = $this->actingAs($user)->get(route('service-list'));
        $indexResponse->assertOk();
        $indexResponse->assertSee(asset('storage/'.$service->image), false);

        $newImage = UploadedFile::fake()->image('service-new.jpg');
        $oldImagePath = $service->image;

        $updateResponse = $this->actingAs($user)->post(route('update-service', $service), [
            'name' => 'AC Cleaning Pro',
            'description' => 'Updated AC unit cleaning.',
            'image' => $newImage,
        ]);

        $updateResponse->assertRedirect(route('service-list'));
        $service->refresh();
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertExists($service->image);

        $this->actingAs($user)->delete(route('delete-service', $service));
        Storage::disk('public')->assertMissing($service->image);
    }
}
