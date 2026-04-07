<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Color', 'Size', 'Material', 'Weight', 'Storage', 'RAM',
            'Screen Size', 'Battery Life', 'Connectivity', 'Warranty',
            'Package Contents', 'Dimensions', 'Model', 'Brand', 'Manufacturer',
            'Country of Origin', 'Year Released', 'Edition', 'Style', 'Pattern',
            'Fabric Type', 'Sleeve Length', 'Neckline', 'Fit', 'Occasion',
            'Season', 'Gender', 'Age Group', 'Features', 'Technology',
            'Compatibility', 'Interface', 'Capacity', 'Speed', 'Format',
            'Resolution', 'Megapixels', 'Zoom', 'Aperture', 'ISO Range',
            'Focal Length', 'Sensor Type', 'Image Stabilization', 'Video Quality',
            'Frame Rate', 'Audio', 'Ports', 'Power', 'Energy Rating',
        ];

        foreach ($attributes as $name) {
            Attribute::updateOrCreate(['name' => $name]);
        }
    }
}
