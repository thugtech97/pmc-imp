<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setting = [
            'id' => 1,
            'api_key' => '',
            'website_name' => 'LORENZANA FOOD CORP.',
            'website_favicon' => 'favicon.ico',
            'company_logo' => 'logo-transparent.png',
            'company_favicon' => '',
            'company_name' => 'LORENZANA FOOD CORP.',
            'company_about' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'company_address' => '907-909 Antel Global Corporate, Brgy. San Antonio, Pasig City, Metro Manila',
            'google_map' => 'https://www.google.com/maps/dir//7708+Saint+Paul+Road+San+Antonio+Village,+Makati+1203+Kalakhang+Maynila/@14.56313,121.0089155,16z/data=!4m8!4m7!1m0!1m5!1m1!1s0x3397c97519bc0a85:0x25ec73a03ec8d19e!2m2!1d121.0089155!2d14.56313',
            'google_recaptcha_sitekey' => '6Lfgj7cUAAAAAJfCgUcLg4pjlAOddrmRPt86tkQK',
            'google_recaptcha_secret' => '6Lfgj7cUAAAAALOaFTbSFgCXpJldFkG8nFET9eRx',
            'data_privacy_title' => 'Privacy-Policy',
            'data_privacy_popup_content' => 'This website uses cookies to ensure you get the best experience.',
            'data_privacy_content' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
            'mobile_no' => '09123456789',
            'fax_no' => '13232107114',
            'tel_no' => '(044) 795-1234',
            'email' => 'support@webfocus.ph',
            'social_media_accounts' => '',
            'copyright' => '2022-2023',
            'user_id' => '1',

        ];

        DB::table('settings')->insert($setting);
    }
}
