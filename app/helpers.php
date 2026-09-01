<?php

if (!function_exists('media_url')) {
    /**
     * Get the public URL for a media file (prioritizing S3/RustFS Object Storage).
     *
     * @param string|null $path
     * @return string|null
     */
    function media_url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $s3Url = rtrim((string) (config('filesystems.disks.s3.url') ?? env('AWS_URL', '')), '/');
        if ($s3Url) {
            return $s3Url . '/' . ltrim($path, '/');
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('unlink_media')) {
    /**
     * Unlink / delete a media file from Object Storage (S3/RustFS) and local storage.
     *
     * @param string|null $path
     * @return bool
     */
    function unlink_media(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // If it's an external URL (starts with http/https), strip our S3 domain if present
        $s3Url = rtrim((string) (config('filesystems.disks.s3.url') ?? env('AWS_URL', '')), '/');
        if ($s3Url && str_starts_with($path, $s3Url)) {
            $path = ltrim(substr($path, strlen($s3Url)), '/');
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return false;
        }

        $cleanPath = ltrim($path, '/');
        $deleted = false;

        // 1. Delete from S3 Object Storage
        try {
            if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($cleanPath)) {
                \Illuminate\Support\Facades\Storage::disk('s3')->delete($cleanPath);
                $deleted = true;
                \Illuminate\Support\Facades\Log::channel('media')->info('S3 file unlinked successfully', [
                    'path' => $cleanPath,
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::channel('media')->warning('Failed to unlink file from S3: ' . $e->getMessage(), [
                'path' => $cleanPath,
            ]);
        }

        // 2. Also check and delete from local storage if exists
        try {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
                $deleted = true;
            }
        } catch (\Throwable $e) {
            // Ignore local errors
        }

        return $deleted;
    }
}

