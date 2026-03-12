<?php

namespace Database\Seeders;

use App\Models\CustomField;
use Illuminate\Database\Seeder;

class CustomFieldSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            [
                'name' => '電話番号',
                'slug' => 'phone',
                'type' => 'text',
                'is_required' => false,
                'sort_order' => 1,
            ],
            [
                'name' => '住所',
                'slug' => 'address',
                'type' => 'textarea',
                'is_required' => false,
                'sort_order' => 2,
            ],
            [
                'name' => '性別',
                'slug' => 'gender',
                'type' => 'radio',
                'options' => ['男性', '女性', 'その他'],
                'is_required' => false,
                'sort_order' => 3,
            ],
            [
                'name' => '生年月日',
                'slug' => 'birthday',
                'type' => 'date',
                'is_required' => false,
                'sort_order' => 4,
            ],
            [
                'name' => '職業',
                'slug' => 'occupation',
                'type' => 'select',
                'options' => ['会社員', '自営業', '学生', '主婦/主夫', 'フリーランス', 'その他'],
                'is_required' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($fields as $field) {
            CustomField::firstOrCreate(
                ['slug' => $field['slug']],
                $field
            );
        }
    }
}
