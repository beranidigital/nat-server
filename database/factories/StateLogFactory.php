<?php

namespace Database\Factories;

use App\Models\AppSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pool\StateLog>
 */
class StateLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_name' => $this->faker->randomElement(AppSettings::getDevicesName()),
            'ip_address' => $this->faker->ipv4,
            'headers' => [
                'user_agent' => $this->faker->userAgent,
                'accept_language' => $this->faker->languageCode,
            ],
            'state' => [
                'temperature' => $this->faker->randomFloat(2, 0, 100),
                'ph' => $this->faker->randomFloat(2, 0, 14),
            ],
        ];
    }
}
