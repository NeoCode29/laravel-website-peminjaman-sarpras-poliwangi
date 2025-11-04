<?php

namespace Database\Factories;

use App\Models\Peminjaman;
use App\Models\Prasarana;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 day', '+1 month');
        $endDate = (clone $startDate)->modify('+1 day');

        return [
            'user_id' => User::factory(),
            'prasarana_id' => Prasarana::factory(),
            'lokasi_custom' => null,
            'event_name' => $this->faker->sentence(3),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => Peminjaman::STATUS_PENDING,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn () => ['status' => Peminjaman::STATUS_APPROVED]);
    }

    public function rejected(): self
    {
        return $this->state(fn () => ['status' => Peminjaman::STATUS_REJECTED]);
    }
}
