<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        if ($request->has('url') && !$request->hasFile('file')) {
            return $this->delete($request);
        }
        
        $file = $request->file('file') ?? $request->file('filepond') ?? null;
        
        if (!$file) {
            return response()->json(['error' => 'No file provided'], 422);
        }
        
        if (!$file->isValid()) {
            return response()->json(['error' => 'Invalid file'], 422);
        }
        
        $allowedMimes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        $ext = $file->getClientOriginalExtension();
        
        if (!in_array(strtolower($ext), $allowedMimes)) {
            return response()->json(['error' => 'Invalid file type'], 422);
        }
        
        $filename = Str::uuid() . '.' . $ext;
        
        $path = $file->storeAs('products', $filename, 'public');
        
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'url' => $url,
            'path' => $path,
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $url = $request->input('url');
        
        if (!$url) {
            $url = $request->query('url');
        }
        
        if (!$url) {
            return response()->json([
                'error' => 'No URL provided', 
                'all' => $request->all(),
                'query' => $request->query()
            ], 422);
        }
        
        $parsed = parse_url($url);
        $path = ltrim($parsed['path'] ?? '', '/storage/');
        
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['message' => 'File deleted']);
    }
}