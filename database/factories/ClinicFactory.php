<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClinicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate a latitude within Nigeria's range
      $latitude = $this->faker->randomFloat(6, 4, 14);

      // Generate a longitude within Nigeria's range
      $longitude = $this->faker->randomFloat(6, 3, 15);
        $cities = [
            "Lagos",
            "Ibadan",
            "Port Harcourt",
            "Rivers",
            "Abuja",
            "Yola",
            "Abia",
            "Osun",
            "Ogun",
            "Lagos",
        ];
        return [
            'name' => $this->faker->lastName() . ' Clinic',
            'about' => $this->faker->text(150),
            'email' => $this->faker->unique()->safeEmail(),
            'tel_no' => $this->faker->phoneNumber(),
            'contact_name' => $this->faker->name(),
            'address' => $this->faker->address(),
            'country' => "Nigeria",
            'city' => $cities[rand(0, 9)],
            'longitude' => $longitude,
            'latitude' => $latitude,
            'display_pic' => $this->faker->imageUrl(),
            'status' => 'Active',
        ];
    }

   
}
