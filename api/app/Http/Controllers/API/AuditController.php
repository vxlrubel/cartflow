<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Audit::with('user');
        if ($request->table_name) {
            $query->where('table_name', $request->table_name);
        }
        $audits = $query->paginate($request->per_page ?? 15);

        return response()->json($audits);
    }
}
