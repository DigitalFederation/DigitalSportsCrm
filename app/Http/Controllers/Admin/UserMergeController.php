<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MergeUserAccountsRequest;
use App\Services\UserMergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserMergeController extends Controller
{
    protected $userMergeService;

    public function __construct(UserMergeService $userMergeService)
    {
        $this->userMergeService = $userMergeService;
    }

    public function show(): View
    {
        return view('web.admin.user.merge');
    }

    public function preview(MergeUserAccountsRequest $request): JsonResponse
    {
        $previewData = $this->userMergeService->previewMerge($request->validated());

        return response()->json($previewData);
    }

    public function merge(MergeUserAccountsRequest $request): JsonResponse
    {
        $result = $this->userMergeService->mergeAccounts($request->validated());

        return response()->json($result);
    }
}
