<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();

        $products = [
            'Wireless Bluetooth Headphones', 'Smart Watch Series 5', 'Laptop Stand Adjustable',
            'USB-C Hub 7-in-1', 'Mechanical Keyboard RGB', 'Gaming Mouse Wireless',
            'Portable Charger 20000mAh', 'Webcam HD 1080p', 'Monitor Light Bar',
            'Desk Organizer Set', 'Noise Canceling Earbuds', 'Smart Speaker Mini',
            'Tablet Stand Holder', 'Cable Management Kit', 'LED Desk Lamp',
            'Ergonomic Mouse Pad', 'Screen Protector Pack', 'Phone Case Premium',
            'Tablet Screen Protector', 'Smart Home Hub', 'Security Camera Indoor',
            'Doorbell Video', 'Smart Thermostat', 'Air Purifier HEPA',
            'Robot Vacuum Cleaner', 'Coffee Maker Programmable', 'Air Fryer Digital',
            'Blender High Speed', 'Juicer Extractor', 'Toaster Oven',
            'Microwave Compact', 'Rice Cooker Smart', 'Slow Cooker 6Qt',
            'Hand Mixer Electric', 'Food Processor 10Cup', 'Knife Set Professional',
            'Cutting Board Set', 'Kitchen Scale Digital', 'Measuring Cups Set',
            'Mixing Bowls Set', 'Storage Containers Glass', 'Water Filter Pitcher',
            'Insulated Water Bottle', 'Lunch Box Bento', 'Food Storage Bags',
            'Reusable Food Wraps', 'Silicone Baking Mat', 'Non-Stick Pan Set',
            'Cast Iron Skillet', 'Stainless Steel Pot', 'Pressure Cooker 6Qt',
        ];

        foreach ($products as $index => $name) {
            $price = rand(500, 50000) / 100;
            $category = $categories->random();
            $brand = $brands->random();

            $product = Product::updateOrCreate(
                ['sku' => 'SKU-'.str_pad($index + 1, 5, '0', STR_PAD_LEFT)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => 'High quality '.$name.' perfect for everyday use. Premium materials and excellent craftsmanship.',
                    'price' => $price,
                    'sale_price' => rand(0, 1) ? $price * (rand(70, 90) / 100) : null,
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'stock' => rand(0, 100),
                    'status' => rand(0, 2) ? 'active' : 'inactive',
                ]
            );

            for ($i = 1; $i <= 3; $i++) {
                $product->images()->updateOrCreate(
                    ['id' => $product->id.$i],
                    ['url' => 'https://picsum.photos/seed/'.$product->id.$i.'/800/600']
                );
            }
        }
    }
}
