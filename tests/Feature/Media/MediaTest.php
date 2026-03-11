<?php

use App\Models\User;
use App\Modules\Shared\Services\MediaService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();
    Storage::fake('public');

    $this->user = User::factory()->create();
    $this->user->assignRole('user');
    $this->token = $this->user->createToken('test')->plainTextToken;
    $this->headers = ['Authorization' => "Bearer {$this->token}"];
});

test('user can upload image', function () {
    $file = UploadedFile::fake()->image('product.jpg', 800, 600);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/media/upload', [
            'file' => $file,
            'folder' => 'products',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['file_id', 'url', 'name']]);
});

test('upload rejects non-image files', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1024);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/media/upload', [
            'file' => $file,
        ]);

    $response->assertStatus(422);
});

test('upload rejects files over 5MB', function () {
    $file = UploadedFile::fake()->image('large.jpg')->size(6000);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/media/upload', [
            'file' => $file,
        ]);

    $response->assertStatus(422);
});

test('media service uploads to local storage when imagekit not configured', function () {
    $service = new MediaService();
    $file = UploadedFile::fake()->image('test.jpg');

    $result = $service->upload($file, 'test');

    expect($result['file_id'])->toStartWith('local_');
    expect($result['url'])->toContain('/storage/');
    expect($result['name'])->toBe('test.jpg');
});

test('media service can delete local file', function () {
    $service = new MediaService();
    $file = UploadedFile::fake()->image('delete-me.jpg');

    $result = $service->upload($file, 'test');
    $deleted = $service->delete($result['file_id']);

    expect($deleted)->toBeTrue();
});

test('user can delete media', function () {
    $file = UploadedFile::fake()->image('todelete.jpg');
    $service = new MediaService();
    $result = $service->upload($file, 'test');

    $response = $this->withHeaders($this->headers)
        ->deleteJson('/api/v1/user/media', [
            'file_id' => $result['file_id'],
        ]);

    $response->assertStatus(200);
});
