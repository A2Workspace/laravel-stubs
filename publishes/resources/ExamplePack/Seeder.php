<?php

namespace Database\Seeders;

use App\Models\Dummy;
use Illuminate\Database\Seeder;

class DummiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Dummy::factory(10)->create();
    }
}
