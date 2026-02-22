<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getAll($request->user());

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->findById($id, $request->user());

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Buyurtma muvaffaqiyatli yaratildi.',
            'data' => $order,
        ], 201);
    }

    public function accept(int $id): JsonResponse
    {
        $order = $this->orderService->updateStatus($id, 'accepted');

        return response()->json([
            'success' => true,
            'message' => 'Buyurtma qabul qilindi.',
            'data' => $order,
        ]);
    }

    public function reject(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateStatus(
            id: $id,
            newStatus: 'rejected',
            notes: $request->validated('notes'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Buyurtma rad etildi.',
            'data' => $order,
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->cancel($id, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Buyurtma bekor qilindi.',
            'data' => $order,
        ]);
    }
}