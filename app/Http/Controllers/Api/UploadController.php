<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\CloudinaryUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CloudinaryUploader $uploader,
    ) {
    }

    public function images(Request $request): JsonResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['image', 'max:5120'],
            'folder' => ['nullable', 'string', 'max:120'],
        ]);

        $urls = $this->uploader->uploadImages(
            (array) $request->file('images', []),
            $request->string('folder')->toString() ?: null,
        );

        if (empty($urls)) {
            return $this->fail('Upload failed. Please try again.', 422);
        }

        return $this->success([
            'urls' => $urls,
        ], 'Images uploaded successfully', 201);
    }

    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'folder' => ['nullable', 'string', 'max:120'],
        ]);

        $file = $request->file('image');
        $url = $this->uploader->uploadImage(
            $file,
            $request->string('folder')->toString() ?: null,
        );

        if (!$url) {
            return $this->fail('Upload failed. Please try again.', 422);
        }

        return $this->success([
            'url' => $url,
        ], 'Image uploaded successfully', 201);
    }
}
