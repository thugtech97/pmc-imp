<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $categories = [
            [
                'parent_id' => '0',
                'name' => 'Patis (Fish Sauce)',
                'slug' => 'patis-fish-sauce',
                'description' => '',
                'status' => 'PUBLISHED',
                'created_by' => 1
            ],
            [
                'parent_id' => '0',
                'name' => 'Suka (Vinegar)',
                'slug' => 'suka-vinegar',
                'description' => '',
                'status' => 'PUBLISHED',
                'created_by' => 1
            ],
            [
                'parent_id' => '0',
                'name' => 'Toyo (Soy Sauce)',
                'slug' => 'toyo-soy-sauce',
                'description' => '',
                'status' => 'PUBLISHED',
                'created_by' => 1
            ],
            [
                'parent_id' => '0',
                'name' => 'Bagoong Isda (Ground Fermented Fish)',
                'slug' => 'bagoong-isda-ground-fermented-fish',
                'description' => '',
                'status' => 'PUBLISHED',
                'created_by' => 1
            ],
            [
                'parent_id' => '0',
                'name' => 'Alamang Guisado (Sauteed Shrimp Paste)',
                'slug' => 'alamang-guisado-sauteed-shrimp-paste',
                'description' => '',
                'status' => 'PUBLISHED',
                'created_by' => 1
            ]
        ];

        DB::table('product_categories')->insert($categories);
    }
}
