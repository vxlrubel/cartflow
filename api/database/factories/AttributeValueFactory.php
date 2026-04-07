<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    public function definition(): array
    {
        $attribute = Attribute::inRandomOrder()->first() ?? Attribute::factory();
        $values = [
            'Color' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow'],
            'Size' => ['S', 'M', 'L', 'XL', 'XXL'],
            'Material' => ['Cotton', 'Polyester', 'Leather', 'Wood', 'Metal'],
            'Weight' => ['1kg', '2kg', '5kg', '10kg'],
            'Storage' => ['64GB', '128GB', '256GB', '512GB', '1TB'],
            'RAM' => ['4GB', '8GB', '16GB', '32GB'],
            'Screen Size' => ['5"', '6"', '7"', '8"'],
        ];

        $attributeName = $attribute->name;
        $value = fake()->randomElement($values[$attributeName] ?? ['Value 1', 'Value 2']);

        return [
            'attribute_id' => $attribute->id,
            'value' => $value,
        ];
    }
}
