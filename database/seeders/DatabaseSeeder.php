<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create sample products
        $products = [
            ['name' => 'Laptop', 'description' => 'High performance laptop', 'price' => 1200.00, 'stock' => 50],
            ['name' => 'Smartphone', 'description' => 'Latest model smartphone', 'price' => 699.99, 'stock' => 100],
            ['name' => 'Headphones', 'description' => 'Noise-cancelling headphones', 'price' => 199.99, 'stock' => 75],
            ['name' => 'Keyboard', 'description' => 'Mechanical gaming keyboard', 'price' => 89.99, 'stock' => 30],
            ['name' => 'Mouse', 'description' => 'Wireless ergonomic mouse', 'price' => 49.99, 'stock' => 60],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Test user: test@example.com / password');
    }
}
