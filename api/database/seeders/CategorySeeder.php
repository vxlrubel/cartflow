<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports',
            'Toys', 'Beauty', 'Automotive', 'Food & Grocery', 'Health',
            'Office', 'Jewelry', 'Pet Supplies', 'Music', 'Movies',
            'Video Games', 'Baby', 'Tools', 'Art', 'Music Instruments',
            'Luggage', 'Outdoor', 'Party Supplies', 'Computers', 'Mobile Phones',
            'Cameras', 'Watches', 'Bags', 'Shoes', 'Furniture',
            'Kitchen', 'Lighting', 'Garden', 'Bedroom', 'Bathroom',
            'Laundry', 'Storage', 'Decor', 'Rugs', 'Curtains',
            'Pillows', 'Mattresses', 'Sofas', 'Tables', 'Chairs',
            'Desks', 'Shelves', 'Mirrors', 'Wall Art', 'Plants',
            'Vases', 'Candles', 'Fragrances', 'Cleaning', 'Organizers',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                ['name' => $name],
                ['parent_id' => $index < 10 && $index > 0 ? ($index < 5 ? Category::where('name', $categories[$index - 1])->first()?->id : null) : null]
            );
        }
    }
}
