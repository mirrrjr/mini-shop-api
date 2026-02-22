<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function getAll(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id, User $user): Order
    {
        $order = Order::with(['items.product'])->findOrFail($id);

        if ($order->user_id !== $user->id) {
            throw new AuthorizationException('Bu buyurtmani ko\'rishga ruxsatingiz yo\'q.');
        }

        return $order;
    }

    public function create(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $totalPrice = 0;
            $itemsToInsert = [];

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "«{$product->name}» mahsulotidan yetarli miqdor yo'q. "
                            . "Mavjud: {$product->stock}, so'ralgan: {$item['quantity']}."
                        ],
                    ]);
                }

                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                $itemsToInsert[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => Order::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($itemsToInsert);

            return $order->load('items.product');
        });
    }

    public function updateStatus(int $id, string $newStatus, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($id, $newStatus, $notes) {
            $order = Order::with('items.product')->lockForUpdate()->findOrFail($id);

            if (!$order->canAdminTransitionTo($newStatus)) {
                throw ValidationException::withMessages([
                    'status' => [
                        "«{$order->status}» holatidan «{$newStatus}» holatiga o'tib bo'lmaydi."
                    ],
                ]);
            }

            if ($newStatus === Order::STATUS_REJECTED) {
                foreach ($order->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'status' => $newStatus,
                'notes' => $notes ?? $order->notes,
            ]);

            return $order->fresh('items.product');
        });
    }

    public function cancel(int $id, User $user): Order
    {
        return DB::transaction(function () use ($id, $user) {
            $order = Order::with('items.product')->lockForUpdate()->findOrFail($id);

            if ($order->user_id !== $user->id) {
                throw new AuthorizationException('Bu buyurtmani bekor qilishga ruxsatingiz yo\'q.');
            }

            if (!$order->canUserCancel()) {
                throw ValidationException::withMessages([
                    'status' => [
                        "Faqat «pending» holatidagi buyurtmani bekor qilish mumkin. "
                        . "Hozirgi holat: «{$order->status}»."
                    ],
                ]);
            }

            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);

            return $order->fresh('items.product');
        });
    }
}