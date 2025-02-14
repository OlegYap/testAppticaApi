<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppTopCategoryRequest;
use App\Services\AppticaService;
use Illuminate\Http\JsonResponse;

class AppPositionController extends Controller
{
    public function __construct(
        private AppticaService $appticaService
    ) {}

    public function index(AppTopCategoryRequest $request): JsonResponse
    {
        $result = $this->appticaService->getPositionsForDate($request->getDate());
        return response()->json($result->toArray());
    }
}
