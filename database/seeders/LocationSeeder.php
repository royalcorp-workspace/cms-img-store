<?php

namespace Database\Seeders;

use App\Models\Location\Province;
use App\Models\Location\City;
use App\Models\Location\SubDistrict;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DKI Jakarta
        $dki = Province::updateOrCreate(
            ['code' => 'DKI'],
            [
                'name' => 'DKI Jakarta',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $jktSel = City::updateOrCreate(
            ['name' => 'Kota Jakarta Selatan'],
            [
                'province_id' => $dki->id,
                'province' => $dki->name,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        SubDistrict::updateOrCreate(
            [
                'city_id' => $jktSel->id,
                'district' => 'Cilandak',
                'sub_district' => 'Cilandak Barat',
            ],
            [
                'province_id' => $dki->id,
                'province' => $dki->name,
                'postal_code' => '12430',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        SubDistrict::updateOrCreate(
            [
                'city_id' => $jktSel->id,
                'district' => 'Kebayoran Baru',
                'sub_district' => 'Melawai',
            ],
            [
                'province_id' => $dki->id,
                'province' => $dki->name,
                'postal_code' => '12160',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // 2. Jawa Barat
        $jabar = Province::updateOrCreate(
            ['code' => 'JABAR'],
            [
                'name' => 'Jawa Barat',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $bandung = City::updateOrCreate(
            ['name' => 'Kota Bandung'],
            [
                'province_id' => $jabar->id,
                'province' => $jabar->name,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        SubDistrict::updateOrCreate(
            [
                'city_id' => $bandung->id,
                'district' => 'Regol',
                'sub_district' => 'Ciseureuh',
            ],
            [
                'province_id' => $jabar->id,
                'province' => $jabar->name,
                'postal_code' => '40256',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // 3. Jawa Timur
        $jatim = Province::updateOrCreate(
            ['code' => 'JATIM'],
            [
                'name' => 'Jawa Timur',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $surabaya = City::updateOrCreate(
            ['name' => 'Kota Surabaya'],
            [
                'province_id' => $jatim->id,
                'province' => $jatim->name,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        SubDistrict::updateOrCreate(
            [
                'city_id' => $surabaya->id,
                'district' => 'Tegalsari',
                'sub_district' => 'Kedungdoro',
            ],
            [
                'province_id' => $jatim->id,
                'province' => $jatim->name,
                'postal_code' => '60261',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }
}
