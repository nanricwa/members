<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestMemberSeeder extends Seeder
{
    public function run(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $standardPlan = Plan::where('slug', 'standard')->first();
        $premiumPlan = Plan::where('slug', 'premium')->first();

        // 無料会員
        $freeMember = Member::firstOrCreate(
            ['email' => 'free@example.com'],
            [
                'name' => '無料太郎',
                'name_kana' => 'ムリョウタロウ',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if ($freePlan && $freeMember->activePlans()->count() === 0) {
            $freeMember->plans()->attach($freePlan->id, [
                'status' => 'active',
                'started_at' => now(),
                'granted_by' => 'registration',
            ]);
        }

        // スタンダード会員
        $stdMember = Member::firstOrCreate(
            ['email' => 'standard@example.com'],
            [
                'name' => 'スタンダード花子',
                'name_kana' => 'スタンダードハナコ',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if ($standardPlan && $stdMember->activePlans()->count() === 0) {
            $stdMember->plans()->attach($standardPlan->id, [
                'status' => 'active',
                'started_at' => now(),
                'granted_by' => 'payment',
            ]);
        }

        // プレミアム会員
        $premiumMember = Member::firstOrCreate(
            ['email' => 'premium@example.com'],
            [
                'name' => 'プレミアム次郎',
                'name_kana' => 'プレミアムジロウ',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if ($premiumPlan && $premiumMember->activePlans()->count() === 0) {
            $premiumMember->plans()->attach($premiumPlan->id, [
                'status' => 'active',
                'started_at' => now(),
                'granted_by' => 'payment',
            ]);
        }

        // 一時停止会員
        Member::firstOrCreate(
            ['email' => 'suspended@example.com'],
            [
                'name' => '停止三郎',
                'name_kana' => 'テイシサブロウ',
                'password' => Hash::make('password'),
                'status' => 'suspended',
                'email_verified_at' => now(),
            ]
        );
    }
}
