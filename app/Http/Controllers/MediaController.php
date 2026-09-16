<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function getUploadUrl(Request $request)
    {
        Log::channel('media')->info('S3 Pre-signed URL requested', [
            'raw_mime'   => $request->mime_type,
            'raw_ext'    => $request->extension,
            'ip'         => $request->ip(),
            'user_id'    => optional(auth()->user())->id,
            'user_name'  => optional(auth()->user())->name,
        ]);

        $request->validate([
            'mime_type' => 'required|string',
            'extension' => 'required|string',
            'folder'    => 'nullable|string|max:50',
        ]);

        $rawMime = strtolower(trim(explode(';', (string) $request->mime_type)[0]));
        $rawExt = strtolower(ltrim(trim((string) $request->extension), '.'));

        // Whitelist allowed MIME types & extensions
        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/pjpeg',
            'image/png',
            'image/x-png',
            'image/webp',
            'image/gif',
            'image/svg+xml',
            'image/avif',
            'image/x-icon',
            'image/vnd.microsoft.icon',
        ];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif', 'ico'];

        if (!in_array($rawMime, $allowedMimes) || !in_array($rawExt, $allowedExtensions)) {
            Log::channel('media')->warning('S3 upload validation rejected: invalid mime/extension', [
                'mime_type' => $rawMime,
                'extension' => $rawExt,
                'ip'        => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Format file tidak didukung. Harap unggah format gambar (JPG, PNG, WEBP, GIF, SVG, AVIF).',
                'errors' => [
                    'mime_type' => ['Format file tidak valid.'],
                ]
            ], 422);
        }

        // Sanitize and normalize folder (default: products)
        $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $request->input('folder', 'products'));
        if (empty($folder)) {
            $folder = 'products';
        }

        try {
            $filePath = $folder . '/' . date('Y/m/') . Str::uuid() . '.' . $rawExt;
            $bucket = config('filesystems.disks.s3.bucket') ?? env('AWS_BUCKET');
            $s3Url = rtrim((string) (config('filesystems.disks.s3.url') ?? env('AWS_URL', '')), '/');

            $client = Storage::disk('s3')->getClient();
            
            $command = $client->getCommand('PutObject', [
                'Bucket'      => $bucket,
                'Key'         => $filePath,
                'ContentType' => $rawMime,
            ]);

            // URL bertanda tangan, valid untuk 5 menit
            $signedRequest = $client->createPresignedRequest($command, '+5 minutes');
            $uploadUrl = (string) $signedRequest->getUri();
            $publicUrl = $s3Url ? ($s3Url . '/' . $filePath) : $filePath;

            Log::channel('media')->info('S3 Pre-signed URL generated successfully', [
                'file_path'  => $filePath,
                'folder'     => $folder,
                'mime_type'  => $rawMime,
                'extension'  => $rawExt,
                'bucket'     => $bucket,
                'upload_url' => $uploadUrl,
                'public_url' => $publicUrl,
                'expires'    => '+5 minutes',
            ]);

            return response()->json([
                'upload_url' => $uploadUrl,
                'file_path'  => $filePath,
                'public_url' => $publicUrl,
            ]);
        } catch (\Throwable $e) {
            Log::channel('media')->error('S3 Pre-signed URL generation failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
                'mime_type' => $rawMime,
                'extension' => $rawExt,
                'bucket'    => $bucket ?? null,
            ]);

            return response()->json([
                'message' => 'Gagal membuat pre-signed upload URL: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|image|max:10240',
            'folder' => 'nullable|string|max:50',
        ]);

        try {
            $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $request->input('folder', 'products'));
            if (empty($folder)) {
                $folder = 'products';
            }

            $file = $request->file('file');
            $rawExt = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $fileName = Str::uuid() . '.' . $rawExt;
            $subDir = $folder . '/' . date('Y/m');

            $path = Storage::disk('s3')->putFileAs($subDir, $file, $fileName);

            $s3Url = rtrim((string) (config('filesystems.disks.s3.url') ?? env('AWS_URL', '')), '/');
            $publicUrl = $s3Url ? ($s3Url . '/' . $path) : $path;

            Log::channel('media')->info('S3 server-side upload completed successfully', [
                'file_path'  => $path,
                'folder'     => $folder,
                'public_url' => $publicUrl,
                'size'       => $file->getSize(),
                'mime_type'  => $file->getMimeType(),
                'ip'         => $request->ip(),
            ]);

            return response()->json([
                'file_path'  => $path,
                'public_url' => $publicUrl,
            ]);
        } catch (\Throwable $e) {
            Log::channel('media')->error('S3 server-side upload failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Gagal mengunggah file ke Object Storage: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::channel('media')->info('S3 file deletion initiated: ' . $id);

            if (Storage::disk('s3')->exists($id)) {
                Storage::disk('s3')->delete($id);
                Log::channel('media')->info('S3 physical file deleted successfully: ' . $id);
            } else {
                Log::channel('media')->info('S3 file path does not exist on disk or already deleted: ' . $id);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Gambar berhasil dihapus dari S3'
            ]);
        } catch (\Throwable $e) {
            Log::channel('media')->error('S3 file deletion failed: ' . $e->getMessage(), [
                'id'        => $id,
                'exception' => get_class($e),
            ]);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
