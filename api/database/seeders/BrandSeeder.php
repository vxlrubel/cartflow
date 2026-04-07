<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Apple', 'Samsung', 'Sony', 'Nike', 'Adidas', 'Puma', 'Reebok',
            'Levi\'s', 'Gap', 'Zara', 'H&M', 'Uniqlo', 'Gucci', 'Prada', 'Louis Vuitton',
            'Ray-Ban', 'Oakley', 'Casio', 'Seiko', 'Timex', ' Fossil', 'Michael Kors',
            'Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'Microsoft', 'Google', 'Amazon',
            'LG', 'Panasonic', 'Toshiba', 'Whirlpool', 'Maytag', 'KitchenAid', 'Cuisinart',
            'Ninja', 'Dyson', 'Vacuums', 'Bose', 'JBL', 'Sennheiser', 'Beats', 'Bose',
            'Canon', 'Nikon', 'Fuji', 'GoPro', 'Logitech', 'Razer', 'Corsair',
            'IKEA', 'Wayfair', 'West Elm', 'Crate & Barrel', 'Pottery Barn', 'Restoration Hardware',
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['name' => $brand]);
        }
    }
}
