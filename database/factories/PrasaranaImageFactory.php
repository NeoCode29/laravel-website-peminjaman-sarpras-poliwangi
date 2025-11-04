<?php

namespace Database\Factories;

use App\Models\Prasarana;
use App\Models\PrasaranaImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrasaranaImageFactory extends Factory
{
    protected $model = PrasaranaImage::class;

    public function definition(): array
    {
        return [
            'prasarana_id' => Prasarana::factory(),
            'image_url' => 'prasarana/images/'.$this->faker->unique()->lexify('image_????.jpg'),
            'sort_order' => 1,
        ];
    }
}
