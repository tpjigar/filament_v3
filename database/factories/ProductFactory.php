<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'sku' => $this->faker->word().'-'.rand(0,111).rand(1, 100),
            'image' => $this->faker->imageUrl(100, 100, 'product', true, 'amazon', false, 'png'),
            'description' => $this->faker->text(),
            'quantity' => $this->faker->randomNumber(),
            'price' => $this->faker->randomFloat(NULL,10, 10000),
            'is_visible' => $this->faker->boolean(),
            'is_featured' => $this->faker->boolean(),
            'type' => $this->faker->randomElement(['deliverable', 'downloadable']),
            'published_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'brand_id' => rand(1, 10),
        ];
    }
}
