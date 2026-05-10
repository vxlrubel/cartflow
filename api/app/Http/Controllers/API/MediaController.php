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
    public function index(Request $request): JsonResponse
    {
        $trashed = $request->query('trashed');

        if ($trashed === 'all') {
            $query = Media::withTrashed();
        } elseif ($trashed) {
            $query = Media::onlyTrashed();
        } else {
            $query = Media::query();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('url', 'like', "%{$search}%")
                  ->orWhere('file->name', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest()->paginate($request->input('per_page', 20)));
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
        $media->delete();

        return response()->json(['message' => 'Media moved to trash']);
    }

    public function restore($id): JsonResponse
    {
        $media = Media::withTrashed()->findOrFail($id);
        $media->restore();

        return response()->json($media);
    }

    public function forceDelete($id): JsonResponse
    {
        $media = Media::withTrashed()->findOrFail($id);

        $file = $media->file;
        if ($file && isset($file['path'])) {
            if (Storage::disk('public')->exists($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }
        }

        $media->forceDelete();

        return response()->json(['message' => 'Media permanently deleted']);
    }

    public function trash(Request $request): JsonResponse
    {
        $query = Media::onlyTrashed();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('url', 'like', '%' . $request->search . '%')
                  ->orWhere('file->name', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json($query->latest()->paginate($request->input('per_page', 20)));
    }
}
