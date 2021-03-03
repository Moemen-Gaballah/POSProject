<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

    	// Seeder For Laratrust .php
    	$this->call(LaratrustSeeder::class);
    	$this->call(UserTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(ProductsTableSeeder::class);
        $this->call(ClientsTableSeeder::class);

        // \App\Models\User::factory(10)->create();
    }
}
