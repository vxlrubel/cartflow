<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $values = [
            'Color' => ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Orange', 'Purple', 'Pink', 'Gray', 'Navy', 'Brown', 'Beige', 'Gold', 'Silver', 'Multicolor'],
            'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', 'One Size', 'Small', 'Medium', 'Large', 'Extra Large'],
            'Material' => ['Cotton', 'Polyester', 'Leather', 'Wood', 'Metal', 'Plastic', 'Glass', 'Ceramic', 'Silicone', 'Rubber', 'Fabric', 'Nylon', 'Spandex', 'Wool', 'Cashmere', 'Bamboo'],
            'Weight' => ['Light', 'Medium', 'Heavy', 'Ultra Light', 'Extra Heavy', '1kg', '2kg', '5kg', '10kg'],
            'Storage' => ['64GB', '128GB', '256GB', '512GB', '1TB', '2TB', '4TB', '8TB', '16GB', '32GB'],
            'RAM' => ['2GB', '4GB', '6GB', '8GB', '12GB', '16GB', '32GB', '64GB'],
            'Screen Size' => ['4 inch', '5 inch', '6 inch', '7 inch', '8 inch', '10 inch', '12 inch', '14 inch', '15 inch', '17 inch', '21 inch', '24 inch', '27 inch', '32 inch'],
            'Battery Life' => ['2 hours', '4 hours', '6 hours', '8 hours', '10 hours', '12 hours', '15 hours', '20 hours', '24 hours', '48 hours'],
            'Connectivity' => ['WiFi', 'Bluetooth', 'USB', 'HDMI', 'VGA', 'DisplayPort', 'Thunderbolt', 'USB-C', 'Lightning', 'Auxiliary', 'NFC', 'GPS', 'Cellular'],
            'Warranty' => ['1 Year', '2 Years', '3 Years', '5 Years', 'Lifetime', '90 Days', '6 Months'],
        ];

        $attributeList = Attribute::all();

        foreach ($attributeList as $attribute) {
            $attributeValues = $values[$attribute->name] ?? ['Value 1', 'Value 2', 'Value 3', 'Value 4', 'Value 5'];

            foreach ($attributeValues as $value) {
                AttributeValue::updateOrCreate(
                    ['attribute_id' => $attribute->id, 'value' => $value],
                    ['attribute_id' => $attribute->id, 'value' => $value]
                );
            }
        }
    }
}
