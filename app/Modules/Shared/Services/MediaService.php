<?php

namespace App\Modules\Shared\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MediaService
{
    /**
     * Upload file to ImageKit or local storage fallback.
     */
    public function upload(UploadedFile $file, string $folder = 'uploads'): array
    {
        if ($this->isImageKitConfigured()) {
            try {
                return $this->uploadToImageKit($file, $folder);
            } catch (Throwable $e) {
                // In local/testing environments, keep media flows reliable even if external SSL/network fails.
                if (app()->environment(['local', 'testing'])) {
                    report($e);
                    return $this->uploadToLocal($file, $folder);
                }

                throw $e;
            }
        }

        return $this->uploadToLocal($file, $folder);
    }

    /**
     * Delete file from ImageKit or local storage.
     */
    public function delete(string $fileId): bool
    {
        if ($this->isImageKitConfigured() && !str_starts_with($fileId, 'local_')) {
            return $this->deleteFromImageKit($fileId);
        }

        // Local deletion
        $path = str_replace('local_', '', $fileId);
        return Storage::disk('public')->delete($path);
    }

    private function isImageKitConfigured(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        return !empty(config('services.imagekit.public_key'))
            && !empty(config('services.imagekit.private_key'))
            && !empty(config('services.imagekit.url_endpoint'));
    }

    private function uploadToImageKit(UploadedFile $file, string $folder): array
    {
        $response = Http::withBasicAuth(config('services.imagekit.private_key'), '')
            ->attach('file', $file->getContent(), $file->getClientOriginalName())
            ->post('https://upload.imagekit.io/api/v1/files/upload', [
                'fileName' => $file->getClientOriginalName(),
                'folder' => "snackzar/{$folder}",
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('ImageKit upload failed: ' . $response->body());
        }

        $data = $response->json();

        return [
            'file_id' => $data['fileId'],
            'url' => $data['url'],
            'thumbnail' => $data['thumbnailUrl'] ?? $data['url'],
            'name' => $data['name'],
            'size' => $data['size'],
        ];
    }

    private function uploadToLocal(UploadedFile $file, string $folder): array
    {
        $path = $file->store($folder, 'public');

        return [
            'file_id' => 'local_' . $path,
            'url' => Storage::disk('public')->url($path),
            'thumbnail' => Storage::disk('public')->url($path),
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ];
    }

    private function deleteFromImageKit(string $fileId): bool
    {
        $response = Http::withBasicAuth(config('services.imagekit.private_key'), '')
            ->delete("https://api.imagekit.io/v1/files/{$fileId}");

        return $response->successful();
    }
}
