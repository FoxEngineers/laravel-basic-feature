<?php

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Passport\Client;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PassportToken>
 */
class PassportTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'user_id' => User::factory(),
            'client_id' => function () {
                return Client::factory()->asPersonalAccessTokenClient()->create()->id;
            },
            'name' => 'Personal Access Token',
            'scopes' => [],
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addMinutes(Constant::TOKENS_EXPIRE_IN),
        ];
    }
}
