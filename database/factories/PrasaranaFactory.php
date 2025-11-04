<?php

namespace Database\Factories;

use App\Models\KategoriPrasarana;
use App\Models\Prasarana;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrasaranaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Prasarana::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => 'Prasarana ' . $this->faker->unique()->word(),
            'kategori_id' => KategoriPrasarana::factory(),
            'description' => $this->faker->sentence(),
            'lokasi' => $this->faker->streetAddress(),
            'kapasitas' => $this->faker->numberBetween(10, 200),
            'status' => 'tersedia',
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the prasarana is in maintenance.
     */
    public function maintenance(): self
    {
        return $this->state(fn () => ['status' => 'maintenance']);
    }

    /**
     * Indicate that the prasarana is damaged.
     */
    public function damaged(): self
    {
        return $this->state(fn () => ['status' => 'rusak']);
    }
}
