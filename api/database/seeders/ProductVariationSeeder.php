<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;

class ProductVariationSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $attributeValues = AttributeValue::all()->groupBy('attribute_id');

        foreach ($products as $product) {
            $combinations = $attributeValues->take(2)->values();

            if ($combinations->isEmpty()) {
                continue;
            }

            $values = $combinations->first();

            foreach ($values->random(min(3, $values->count()), false) as $value) {
                $variation = ProductVariation::updateOrCreate(
                    ['sku' => 'VAR-'.$product->id.'-'.$value->id],
                    [
                        'product_id' => $product->id,
                        'sku' => 'VAR-'.$product->id.'-'.$value->id,
                        'price' => $product->price * (rand(80, 120) / 100),
                        'stock' => rand(0, 30),
                    ]
                );
                $variation->attributeValues()->sync([$value->id]);
            }
        }
    }
}
