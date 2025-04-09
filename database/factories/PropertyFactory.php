<?php

namespace factories;

use Gongarce\ProductProps\Models\Property;
use Lunar\Database\Factories\BaseFactory;

/**
 * @extends \Lunar\Database\Factories\BaseFactory<\App\Models\Property>
 */
class PropertyFactory extends BaseFactory
{
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => collect([
                'en' => $this->faker->name(),
            ]),
        ];
    }
}
