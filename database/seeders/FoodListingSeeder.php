<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FoodListing;
use App\Models\Establishment;
use Carbon\Carbon;

class FoodListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first establishment to use as a reference
        $establishment = Establishment::first();
        
        if (!$establishment) {
            // Create a sample establishment if none exists
            $establishment = Establishment::create([
                'establishment_id' => \Illuminate\Support\Str::uuid(),
                'business_name' => 'Sample Food Store',
                'owner_fname' => 'John',
                'owner_lname' => 'Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'address' => '123 Main St, City',
                'business_type' => 'grocery',
                'is_verified' => true,
            ]);
        }

        $foodListings = [
            // Fruits & Vegetables
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Fresh Bananas',
                'description' => 'Ripe and sweet bananas, perfect for snacking or baking',
                'category' => 'fruits-vegetables',
                'quantity' => 50,
                'original_price' => 25.00,
                'discount_percentage' => 20,
                'discounted_price' => 20.00,
                'expiry_date' => Carbon::now()->addDays(3),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => false,
                'status' => 'active',
            ],
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Fresh Tomatoes',
                'description' => 'Juicy red tomatoes, great for salads and cooking',
                'category' => 'fruits-vegetables',
                'quantity' => 30,
                'original_price' => 40.00,
                'discount_percentage' => 15,
                'discounted_price' => 34.00,
                'expiry_date' => Carbon::now()->addDays(2),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => true,
                'status' => 'active',
            ],
            
            // Baked Goods
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Fresh Bread Loaves',
                'description' => 'Artisan bread baked fresh daily',
                'category' => 'baked-goods',
                'quantity' => 15,
                'original_price' => 50.00,
                'discount_percentage' => 30,
                'discounted_price' => 35.00,
                'expiry_date' => Carbon::now()->addDays(1),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => false,
                'status' => 'active',
            ],
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Chocolate Croissants',
                'description' => 'Buttery croissants filled with rich chocolate',
                'category' => 'baked-goods',
                'quantity' => 20,
                'original_price' => 35.00,
                'discount_percentage' => 25,
                'discounted_price' => 26.25,
                'expiry_date' => Carbon::now()->addDays(1),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => true,
                'status' => 'active',
            ],
            
            // Cooked Meals
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Chicken Adobo',
                'description' => 'Traditional Filipino chicken adobo, ready to eat',
                'category' => 'cooked-meals',
                'quantity' => 8,
                'original_price' => 120.00,
                'discount_percentage' => 20,
                'discounted_price' => 96.00,
                'expiry_date' => Carbon::now()->addDays(1),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => true,
                'status' => 'active',
            ],
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Beef Stir Fry',
                'description' => 'Fresh beef stir fry with vegetables',
                'category' => 'cooked-meals',
                'quantity' => 6,
                'original_price' => 150.00,
                'discount_percentage' => 15,
                'discounted_price' => 127.50,
                'expiry_date' => Carbon::now()->addDays(1),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => false,
                'status' => 'active',
            ],
            
            // Packaged Goods
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Canned Corn',
                'description' => 'Sweet corn kernels in water, perfect for cooking',
                'category' => 'packaged-goods',
                'quantity' => 25,
                'original_price' => 30.00,
                'discount_percentage' => 10,
                'discounted_price' => 27.00,
                'expiry_date' => Carbon::now()->addDays(30),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => true,
                'status' => 'active',
            ],
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Pasta Pack',
                'description' => 'Premium pasta noodles, 500g pack',
                'category' => 'packaged-goods',
                'quantity' => 12,
                'original_price' => 45.00,
                'discount_percentage' => 20,
                'discounted_price' => 36.00,
                'expiry_date' => Carbon::now()->addDays(60),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => true,
                'status' => 'active',
            ],
            
            // Beverages
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Fresh Orange Juice',
                'description' => 'Freshly squeezed orange juice, no preservatives',
                'category' => 'beverages',
                'quantity' => 10,
                'original_price' => 60.00,
                'discount_percentage' => 25,
                'discounted_price' => 45.00,
                'expiry_date' => Carbon::now()->addDays(2),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => true,
                'status' => 'active',
            ],
            [
                'establishment_id' => $establishment->establishment_id,
                'name' => 'Green Tea',
                'description' => 'Premium green tea bags, 20 count',
                'category' => 'beverages',
                'quantity' => 15,
                'original_price' => 80.00,
                'discount_percentage' => 15,
                'discounted_price' => 68.00,
                'expiry_date' => Carbon::now()->addDays(90),
                'address' => '123 Main St, City',
                'pickup_available' => true,
                'delivery_available' => false,
                'status' => 'active',
            ],
        ];

        foreach ($foodListings as $listing) {
            FoodListing::create($listing);
        }
    }
}
