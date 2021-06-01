<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;

class UrlFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Url::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'slug'  => $this->faker->domainWord(), //ean8(),
            'url'   => $this->faker->url(),
            'valid' => Carbon::now()->addMinutes(random_int(1, 100)),
        ];
    }
}
