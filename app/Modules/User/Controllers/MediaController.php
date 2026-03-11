<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Services\MediaService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Media
 *
 * APIs for uploading and deleting media files (images).
 */
class MediaController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly MediaService $mediaService
    ) {}

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif'],
            'folder' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $this->mediaService->upload(
            $request->file('file'),
            $request->input('folder', 'uploads')
        );

        return $this->created($result, 'File uploaded.');
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'file_id' => ['required', 'string'],
        ]);

        $this->mediaService->delete($request->input('file_id'));

        return $this->noContent('File deleted.');
    }
}
