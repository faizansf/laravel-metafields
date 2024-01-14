<?php

namespace FaizanSf\LaravelMetafields\Tests\TestSupport\Factories;

use FaizanSf\LaravelMetafields\Tests\TestSupport\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
