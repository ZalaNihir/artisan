<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class TeacherFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Teacher - '.fake()->name(),
            'email' => 'teacher'.fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'employee_id'=> Str::random(10),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'joining_date' => fake()->dateTime(),
            'qualification'=> 'Random Q',
        ];
    }
}
