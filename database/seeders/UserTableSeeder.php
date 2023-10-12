<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \App\Models\User::factory()->create(['email' => 'maiconcabral.mcm@gmail.com']);
        \App\Models\User::factory(10)->create();
    }
}