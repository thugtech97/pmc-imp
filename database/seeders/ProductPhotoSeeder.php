<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class ProductPhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $products_photos = [
            [
                'product_id' => '1',
                'name' => '',
                'description' => '',
                'path' => \URL::to('/').'/theme/images/products/prod1.jpg',
                'status' => 'PUBLISHED',
                'is_primary' => '1',
                'created_by' => 1
            ],
            [
                'product_id' => '2',
                'name' => '',
                'description' => '',
                'path' => \URL::to('/').'/theme/images/products/prod2.jpg',
                'status' => 'PUBLISHED',
                'is_primary' => '1',
                'created_by' => 1
            ],
            [
                'product_id' => '3',
                'name' => '',
                'description' => '',
                'path' => \URL::to('/').'/theme/images/products/prod3.jpg',
                'status' => 'PUBLISHED',
                'is_primary' => '1',
                'created_by' => 1
            ],
            [
                'product_id' => '4',
                'name' => '',
                'description' => '',
                'path' => \URL::to('/').'/theme/images/products/prod4.jpg',
                'status' => 'PUBLISHED',
                'is_primary' => '1',
                'created_by' => 1
            ],
            [
                'product_id' => '5',
                'name' => '',
                'description' => '',
                'path' => \URL::to('/').'/theme/images/products/prod5.jpg',
                'status' => 'PUBLISHED',
                'is_primary' => '1',
                'created_by' => 1
            ],
            [
                'product_id' => '6',
                'name' => '',
                'description' => '',
                'path' => \URL::to('/').'/theme/images/products/prod6.jpg',
                'status' => 'PUBLISHED',
                'is_primary' => '1',
                'created_by' => 1
            ],
            [
                'product_id' => '7',
                'name' => '',
                'description' => '',
                'path' => \URL::to('/').'/theme/images/products/prod7.jpg',
                'status' => 'PUBLISHED',
                'is_primary' => '1',
                'created_by' => 1
            ],
            [
                'product_id' => '8',
                'name' => '',
                'description' => '',
                'path' => \URL::to('/').'/theme/images/products/prod8.jpg',
                'status' => 'PUBLISHED',
                'is_primary' => '1',
                'created_by' => 1
            ]
            

        ];

        DB::table('product_photos')->insert($products_photos);
    }
}
