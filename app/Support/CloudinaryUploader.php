<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class CloudinaryUploader
{
    public function uploadImages(array $files, ?string $folder = null): array
    {
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');
        $defaultFolder = (string) config('services.cloudinary.folder', 'ceylon-ads');
        $targetFolder = $folder ?: $defaultFolder;

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            return [];
        }

        $urls = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $tempPath = $this->resizeImageToTarget($file->getPathname());
            if (!$tempPath) {
                continue;
            }

            $uploaded = $this->uploadToCloudinary($tempPath, $cloudName, $apiKey, $apiSecret, $targetFolder);
            @unlink($tempPath);
            if ($uploaded !== null) {
                $urls[] = $uploaded;
            }
        }

        return $urls;
    }

    public function uploadImage(UploadedFile $file, ?string $folder = null): ?string
    {
        $urls = $this->uploadImages([$file], $folder);
        return $urls[0] ?? null;
    }

    private function resizeImageToTarget(string $path): ?string
    {
        $info = @getimagesize($path);
        if (!$info) {
            return null;
        }

        [$width, $height] = $info;
        $maxWidth = 1400;
        $ratio = $width > $maxWidth ? ($maxWidth / $width) : 1;
        $newWidth = (int) max(1, round($width * $ratio));
        $newHeight = (int) max(1, round($height * $ratio));

        $src = $this->createImageResource($path, $info['mime']);
        if (!$src) {
            return null;
        }

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($src);

        $targetBytes = 110 * 1024;
        $quality = 82;
        $tempPath = tempnam(sys_get_temp_dir(), 'adimg_').'.jpg';
        imagejpeg($dst, $tempPath, $quality);

        while (filesize($tempPath) > $targetBytes && $quality > 45) {
            $quality -= 7;
            imagejpeg($dst, $tempPath, $quality);
        }

        if (filesize($tempPath) > $targetBytes) {
            $scale = 0.85;
            $scaledWidth = (int) max(1, round($newWidth * $scale));
            $scaledHeight = (int) max(1, round($newHeight * $scale));
            $scaled = imagecreatetruecolor($scaledWidth, $scaledHeight);
            imagecopyresampled($scaled, $dst, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $newWidth, $newHeight);
            imagedestroy($dst);
            $dst = $scaled;
            imagejpeg($dst, $tempPath, $quality);
        }

        imagedestroy($dst);
        return $tempPath;
    }

    private function createImageResource(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif' => @imagecreatefromgif($path),
            default => null,
        };
    }

    private function uploadToCloudinary(string $path, string $cloudName, string $apiKey, string $apiSecret, string $folder): ?string
    {
        $timestamp = time();
        $signatureBase = "folder={$folder}&timestamp={$timestamp}{$apiSecret}";
        $signature = sha1($signatureBase);

        $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";
        $post = [
            'file' => new \CURLFile($path, 'image/jpeg', basename($path)),
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'folder' => $folder,
            'signature' => $signature,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300 || !$response) {
            return null;
        }

        $json = json_decode($response, true);
        if (!is_array($json) || empty($json['secure_url'])) {
            return null;
        }

        return (string) $json['secure_url'];
    }
}
