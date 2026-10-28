<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $homeHTML = '
            <div class="container-fluid">
                <div class="row align-items-lg-center col-mb-30 bg-orange">
                    <div class="col-lg-6 px-lg-0 min-vh-50 min-vh-lg-100" style="background: url('.\URL::to('/').'/theme/images/misc/Lorins-Founders-home2.jpg) no-repeat center center; background-size: cover;">
                    </div>
                    
                    <div class="col-lg-6 px-lg-5 py-5 text-center">
                        <h2 class="display-4 fw-normal loren-title">About <en>Us</en></h2>
                        <p>Its founder, Mr. Felipe Lorenzana, started his Bagoong business with his family catering to farmers and miners of Northern Luzon. Lorenzana Food Corporation (LFC) is one of the more popular and experienced Filipino food manufacturers in the Philippines. It was established as early as 1908 as a BAGOONG manufacturer.<br>
                        <img src="'.\URL::to('/').'/theme/images/misc/home-intro.jpg" /></p>
                        <a href="products.htm" class="button button-border m-0 button-white border-width-1 border-default h-bg-color bg-white rounded loren-shop-btn-white">Shop Now</a>
                    </div>
                </div>
            </div>

            {Featured Products}

            <div class="container-fluid">
                <div class="row align-items-lg-center col-mb-30 bg-orange">
                    <div class="col-lg-6 px-lg-5 py-5 text-center">
                        <h3 class="h1 mb-4 fw-normal loren-title">LORINS <br><en>PATIS PURO</en></h3>
                        <p>Lorins Patis Puro is classified as a high protein fish Sauce that has a protein content of greater than 7.5% that is many times the amount of the fish flavor giving it a stronger and bolder umami flavor.</p>
                        <a href="products.htm" class="button button-border m-0 button-white border-width-1 border-default h-bg-color bg-white rounded loren-shop-btn-white">Shop Now</a>
                    </div>
                    
                    <div class="col-lg-6 px-lg-0 min-vh-50 min-vh-lg-75" style="background: url('.\URL::to('/').'/theme/images/misc/section.jpg) no-repeat center center; background-size: cover;">
                    </div>
                </div>
            </div>

            <div class="section custom-bg mt-3 mb-0" style="--custom-bg: #F3F3ED; padding: 100px 0;">
                <div class="container">
                    <div id="shop" class="shop row gutter-30 col-mb-30 mt-3">
                        
                        <!-- Heading Title -->
                        <div class="text-center mb-5">
                            <h2 class="h1 fw-normal mb-4 loren-title loren-title-white">Recipes</h2>
                        </div>

                        <!-- Product 1 -->
                        <div class="product col-lg-4 col-md-4 col-sm-6 col-12">
                            <div class="grid-inner">
                                <div class="product-image">
                                    <a href="#"><img src="'.\URL::to('/').'/theme/images/recipes/rec1.jpg" alt="Light Grey Sofa"></a>
                                </div>
                            </div>
                        </div>

                        <!-- Product 2 -->
                        <div class="product col-lg-4 col-md-4 col-sm-6 col-12">
                            <div class="grid-inner">
                                <div class="product-image">
                                    <a href="#"><img src="'.\URL::to('/').'/theme/images/recipes/rec2.jpg" alt="Celling Lights"></a>
                                </div>
                            </div>
                        </div>

                        <!-- Product 3 -->
                        <div class="product col-lg-4 col-md-4 col-sm-6 col-12">
                            <div class="grid-inner">
                                <div class="product-image">
                                    <a href="#"><img src="'.\URL::to('/').'/theme/images/recipes/rec3.jpg" alt="High Stand Chair"></a>
                                </div>
                            </div>
                        </div>

                        <!-- Product 4 -->
                        <div class="product col-lg-4 col-md-4 col-sm-6 col-12">
                            <div class="grid-inner">
                                <div class="product-image">
                                    <a href="#"><img src="'.\URL::to('/').'/theme/images/recipes/rec4.jpg" alt="Dining Sofa with Tea-table"></a>
                                </div>
                            </div>
                        </div>

                        <!-- Product 5 -->
                        <div class="product col-lg-4 col-md-4 col-sm-6 col-12">
                            <div class="grid-inner">
                                <div class="product-image">
                                    <a href="#"><img src="'.\URL::to('/').'/theme/images/recipes/rec5.jpg" alt="Bathroom Cloths Shelves"></a>
                                </div>
                            </div>
                        </div>

                        <!-- Product 1 -->
                        <div class="product col-lg-4 col-md-4 col-sm-6 col-12">
                            <div class="grid-inner">
                                <div class="product-image">
                                    <a href="#"><img src="'.\URL::to('/').'/theme/images/recipes/rec6.jpg" alt="Golden Lamp for Room"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';


        $aboutHTML = '
        <div class="container-fluid">
            <div class="row align-items-lg-center col-mb-30 bg-orange">

                <div class="col-lg-6 px-lg-0 min-vh-50 min-vh-lg-100" style="background: url('.\URL::to('/').'/theme/images/misc/Lorins-Founders-home2.jpg) no-repeat center center; background-size: cover;">
                </div>

                <div class="col-lg-6 px-lg-5 py-5 text-center">
                    <h2 class="display-4 fw-normal loren-title">LORENZANA FOOD CORPORATION</h2>
                    <p>From a cottage industry in Tagudin, Ilocos Sur, LFC has grown, and has evolved. It has evolved to be the market leader in the domestic Patis and Bagoong industry with its LORINS and LORENZANA brands.</p>
                    <p>Modern facilities and the commitment to make Patis and Bagoong world class products have not only brought recognition to LFC in the global market but also pride to Filipinos overseas as Lorenzana continues to make Philippine Bagoong and Patis word-class.</p>
                </div>
            </div>
        </div>

        <div class="about-story">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center pt-6 mb-5">
                            <h2 class="h1 fw-normal mb-4 loren-title loren-title-white"><en>Our</en> Story</h2>
                        </div>

                        <section class="ps-timeline-sec">
                            <div class="container">
                                <ol class="ps-timeline">
                                    <li>
                                        <div class="img-handler-top text-center">
                                            <h2>1908</h2>
                                            <p>Do you have a recent injury or long term pain?</p>
                                        </div>
                                        <span class="ps-sp-top"></span>
                                    </li>
                                    <li>
                                        <div class="img-handler-bot text-center">
                                            <h2>1936</h2>
                                            <p>Have you tried Physiotherapy, Chiropractor or your GP without the pain free results?</p>
                                        </div>
                                        <span class="ps-sp-bot"></span>
                                    </li>
                                    <li>
                                        <div class="img-handler-top text-center">
                                            <h2>1938</h2>
                                            <p>Let Physology assess and treat your pain with our trusted revolusionary approach.</p>
                                        </div>
                                        <span class="ps-sp-top"></span>
                                    </li>
                                    <li>
                                        <div class="img-handler-bot text-center">
                                            <h2>1942</h2>
                                            <p>Join our happy family of pain free clients.</p>
                                        </div>
                                        <span class="ps-sp-bot"></span>
                                    </li>
                                    <li>
                                        <div class="img-handler-top text-center">
                                            <h2>1972</h2>
                                            <p>Let Physology assess and treat your pain with our trusted revolusionary approach.</p>
                                        </div>
                                        <span class="ps-sp-top"></span>
                                    </li>
                                    <li>
                                        <div class="img-handler-bot text-center">
                                            <h2>1981</h2>
                                            <p>Join our happy family of pain free clients.</p>
                                        </div>
                                        <span class="ps-sp-bot"></span>
                                    </li>
                                    <li>
                                        <div class="img-handler-top text-center">
                                            <h2>2010</h2>
                                            <p>Let Physology assess and treat your pain with our trusted revolusionary approach.</p>
                                        </div>
                                        <span class="ps-sp-top"></span>
                                    </li>
                                </ol>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>';


        $contactUsHTML = '
            <h3>Contact Details</h3>

            <iframe class="mb-4" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.6351879919657!2d121.0079802148399!3d14.562842589826564!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c97529aecac7%3A0xf575bfff50902c78!2s7708%20Saint%20Paul%20Road%2C%20Village%2C%20Makati%2C%201203%20Kalakhang%20Maynila!5e0!3m2!1sen!2sph!4v1605668109563!5m2!1sen!2sph" width="100%" height="70" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>

            <div class="row topmargin d-none">
                <div class="col-lg-6">
                    <address>
                        <abbr title="Address">Address:</abbr><br>
                        444a EDSA, Guadalupe Viejo, Makati City, Philippines 1211
                    </address>
                </div>
                <div class="col-lg-6">
                    <p><abbr title="Email Address">Email:</abbr><br>info@vanguard.edu.ph</p>
                </div>
                <div class="col-lg-6">
                    <p class="nomargin"><abbr title="Phone Number">Phone:</abbr><br>(632) 8-1234-4567</p>
                </div>
                <div class="col-lg-6">
                    <p class="nomargin"><abbr title="Phone Number">Fax:</abbr><br>(632) 8-1234-4567</p>
                </div>
            </div>';

        $footerHTML = '
            <div class="container">
                <div class="footer-widgets-wrap py-lg-6">
                    <div class="row">
                        <div class="col-lg-8 col-md-7">
                            <p><img src="'.\URL::to('/').'/theme/images/misc/lorins-logo.jpg" /><img src="'.\URL::to('/').'/theme/images/misc/lorenzana-logo.jpg" /></p>
                            <h3>LORENZANA FOOD CORP.</h3>
                            <ul>
                                <li>Lot 6720 Brgy San Joaquin Sto Tomas Batangas </li>
                                <li>Royal Goldcraft Warehouse Compound, Magsaysay <br>Road, Brgy. San Antonio San Pedro 4023 Laguna</li>
                            </ul>
                        </div>
                        <div class="col-lg-4 col-md-5">
                            <h2>General Inquiries</h2>
                            Email: sales@lorenzana.com.ph<br>
                            Phone: 8568- 3440<br>
                            <br>
                            <a href="#" class="social-icon si-colored si-instagram" title="Instagram">
                                <i class="icon-instagram"></i>
                                <i class="icon-instagram"></i>
                            </a>
                            <a href="#" class="social-icon si-colored si-facebook">
                                <i class="icon-facebook"></i>
                                <i class="icon-facebook"></i>
                            </a>
                            <a href="#" class="social-icon si-colored si-twitter" title="Twitter">
                                <i class="icon-twitter"></i>
                                <i class="icon-twitter"></i>
                            </a>
                            <a href="#" class="social-icon si-colored si-youtube" title="Youtube">
                                <i class="icon-youtube"></i>
                                <i class="icon-youtube"></i>
                            </a>
                            <a href="#" class="social-icon si-colored si-tiktok" title="TikTok">
                                <i class="icon-tiktok"></i>
                                <i class="icon-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>';

      
        $pages = [
            [
                'parent_page_id' => 0,
                'album_id' => 1,
                'slug' => 'home',
                'name' => 'Home',
                'label' => 'Home',
                'contents' => $homeHTML,
                'status' => 'PUBLISHED',
                'page_type' => 'default',
                'image_url' => '',
                'meta_title' => 'Home',
                'meta_keyword' => 'home',
                'meta_description' => 'Home page',
                'user_id' => 1,
                'template' => 'home',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'parent_page_id' => 0,
                'album_id' => 0,
                'slug' => 'about-us',
                'name' => 'About Us',
                'label' => 'About Us',
                'contents' => $aboutHTML,
                'status' => 'PUBLISHED',
                'page_type' => 'standard',
                'image_url' => '',
                'meta_title' => 'About Us',
                'meta_keyword' => 'About Us',
                'meta_description' => 'About Us page',
                'user_id' => 1,
                'template' => '',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],

            [
                'parent_page_id' => 0,
                'album_id' => 0,
                'slug' => 'contact-us',
                'name' => 'Contact Us',
                'label' => 'Contact Us',
                'contents' => $contactUsHTML,
                'status' => 'PUBLISHED',
                'page_type' => 'standard',
                'image_url' => '',
                'meta_title' => 'Contact Us',
                'meta_keyword' => 'Contact Us',
                'meta_description' => 'Contact Us page',
                'user_id' => 1,
                'template' => 'contact-us',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'parent_page_id' => 0,
                'album_id' => 0,
                'slug' => 'news',
                'name' => 'News and Updates',
                'label' => 'News and Updates',
                'contents' => '',
                'status' => 'PUBLISHED',
                'page_type' => 'customize',
                'image_url' => '',
                'meta_title' => 'News',
                'meta_keyword' => 'news',
                'meta_description' => 'News page',
                'user_id' => 1,
                'template' => 'news',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'parent_page_id' => 0,
                'album_id' => 0,
                'slug' => 'footer',
                'name' => 'Footer',
                'label' => 'footer',
                'contents' => $footerHTML,
                'status' => 'PUBLISHED',
                'page_type' => 'default',
                'image_url' => '',
                'meta_title' => '',
                'meta_keyword' => '',
                'meta_description' => '',
                'user_id' => 1,
                'template' => '',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        ];

        DB::table('pages')->insert($pages);
    }
}
