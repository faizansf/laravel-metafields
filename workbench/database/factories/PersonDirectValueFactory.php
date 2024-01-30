<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\PersonDirectValue;

/**
 * @template TModel of PersonDirectValue
 *
 * @extends Factory<TModel>
 */
class PersonDirectValueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = PersonDirectValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
