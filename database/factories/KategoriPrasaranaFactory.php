<?php

namespace Database\Factories;

use App\Models\KategoriPrasarana;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriPrasaranaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = KategoriPrasarana::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'icon' => null,
            'is_active' => true,
        ];
    }
}
