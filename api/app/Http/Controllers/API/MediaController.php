<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Media::latest()->paginate(20));
    }

    public function show(Media $media): JsonResponse
    {
        return response()->json($media);
    }

    public function upload(Request $request): JsonResponse
    {
        $data = [];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $ext;
            $path = $file->storeAs('media', $filename, 'public');
            $url = Storage::disk('public')->url($path);

            $data = [
                'url' => $url,
                'file' => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'ext' => $ext,
                    'path' => $path,
                ],
            ];
        } elseif ($request->filled('url')) {
            $data = [
                'url' => $request->input('url'),
                'file' => $request->input('file', []),
            ];
        } elseif ($request->filled('file')) {
            $data = [
                'url' => $request->input('url', ''),
                'file' => $request->input('file'),
            ];
        }

        if (empty($data)) {
            return response()->json(['error' => 'No file or URL provided'], 422);
        }

        $media = Media::create($data);

        return response()->json($media, 201);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $data = [];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $ext;
            $path = $file->storeAs('media', $filename, 'public');
            $url = Storage::disk('public')->url($path);

            $data = [
                'url' => $url,
                'file' => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'ext' => $ext,
                    'path' => $path,
                ],
            ];
        } else {
            if ($request->filled('url')) {
                $data['url'] = $request->input('url');
            }
            if ($request->filled('file')) {
                $data['file'] = $request->input('file');
            }
        }

        $media->update($data);

        return response()->json($media);
    }

    public function destroy(Media $media): JsonResponse
    {
        $file = $media->file;
        if ($file && isset($file['path'])) {
            if (Storage::disk('public')->exists($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }
        }

        $media->delete();

        return response()->json(['message' => 'Media deleted']);
    }
}
