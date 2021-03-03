<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = ['ahmed', 'mohamed'];

        foreach ($clients as $client) {
        	\App\Models\Client::create([
        		'name' => $client,
        		'phone' => '01234567890',
        		'address' => 'haram',
        	]);
        } // end for each
    } // end of run
}
