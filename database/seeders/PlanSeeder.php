<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => '無料プラン',
                'slug' => 'free',
                'description' => '基本的なコンテンツにアクセスできる無料プランです。',
                'type' => 'free',
                'price' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'スタンダードプラン',
                'slug' => 'standard',
                'description' => 'すべてのコンテンツにアクセスできるスタンダードプランです。',
                'type' => 'paid_recurring',
                'price' => 2980,
                'sort_order' => 2,
            ],
            [
                'name' => 'プレミアムプラン',
                'slug' => 'premium',
                'description' => '限定コンテンツを含む全機能にアクセスできるプレミアムプランです。',
                'type' => 'paid_recurring',
                'price' => 9800,
                'sort_order' => 3,
            ],
            [
                'name' => '買い切りコース',
                'slug' => 'onetime',
                'description' => '一度の支払いで永久にアクセスできるコースです。',
                'type' => 'paid_once',
                'price' => 29800,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
