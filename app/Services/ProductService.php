<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Product::latest()->paginate($perPage);
    }

    public function findById(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->findById($id);
        $product->update($data);

        return $product->fresh();
    }

    public function delete(int $id): void
    {
        $product = $this->findById($id);
        $product->delete();
    }
}
