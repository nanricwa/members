<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Download;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageContent;
use App\Models\Plan;
use App\Models\RegistrationForm;
use Illuminate\Database\Seeder;

class TestContentSeeder extends Seeder
{
    public function run(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $standardPlan = Plan::where('slug', 'standard')->first();
        $premiumPlan = Plan::where('slug', 'premium')->first();

        // --- カテゴリ ---
        $gettingStarted = Category::firstOrCreate(
            ['slug' => 'getting-started'],
            ['name' => 'はじめに', 'sort_order' => 1, 'is_published' => true]
        );

        $basicCourse = Category::firstOrCreate(
            ['slug' => 'basic-course'],
            ['name' => '基礎コース', 'sort_order' => 2, 'is_published' => true]
        );

        $advancedCourse = Category::firstOrCreate(
            ['slug' => 'advanced-course'],
            ['name' => '上級コース', 'sort_order' => 3, 'is_published' => true]
        );

        // カテゴリ×プランアクセス制御
        if ($freePlan) {
            $gettingStarted->plans()->syncWithoutDetaching([$freePlan->id]);
        }
        if ($standardPlan) {
            $basicCourse->plans()->syncWithoutDetaching([$standardPlan->id]);
            if ($premiumPlan) {
                $basicCourse->plans()->syncWithoutDetaching([$premiumPlan->id]);
            }
        }
        if ($premiumPlan) {
            $advancedCourse->plans()->syncWithoutDetaching([$premiumPlan->id]);
        }

        // --- ページ: はじめに ---
        $welcomePage = Page::firstOrCreate(
            ['slug' => 'welcome'],
            [
                'category_id' => $gettingStarted->id,
                'title' => 'ようこそ！会員サイトへ',
                'type' => 'text',
                'is_published' => true,
                'published_at' => now(),
                'sort_order' => 1,
            ]
        );

        PageContent::firstOrCreate(
            ['page_id' => $welcomePage->id, 'sort_order' => 1],
            [
                'type' => 'heading',
                'body' => 'ようこそ、会員サイトへ！',
            ]
        );

        PageContent::firstOrCreate(
            ['page_id' => $welcomePage->id, 'sort_order' => 2],
            [
                'type' => 'text',
                'body' => '<p>このサイトでは、あなたの学習をサポートするためのコンテンツを提供しています。</p><p>左のメニューからカテゴリを選んで、コンテンツをご覧ください。</p>',
            ]
        );

        // --- ページ: 動画サンプル ---
        $videoPage = Page::firstOrCreate(
            ['slug' => 'intro-video'],
            [
                'category_id' => $basicCourse->id,
                'title' => 'イントロダクション動画',
                'type' => 'video',
                'is_published' => true,
                'published_at' => now(),
                'sort_order' => 1,
            ]
        );

        PageContent::firstOrCreate(
            ['page_id' => $videoPage->id, 'sort_order' => 1],
            [
                'type' => 'text',
                'body' => '<p>基礎コースのイントロダクション動画です。まずはこちらからご覧ください。</p>',
            ]
        );

        PageContent::firstOrCreate(
            ['page_id' => $videoPage->id, 'sort_order' => 2],
            [
                'type' => 'video',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'video_provider' => 'youtube',
            ]
        );

        // --- ダウンロードファイル ---
        $download = Download::firstOrCreate(
            ['title' => 'サンプルPDF教材'],
            [
                'description' => 'テスト用のPDFファイルです。',
                'file_path' => 'downloads/sample.pdf',
                'original_filename' => 'sample.pdf',
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
                'is_active' => true,
            ]
        );

        // --- ページ: DLサンプル ---
        $dlPage = Page::firstOrCreate(
            ['slug' => 'download-materials'],
            [
                'category_id' => $advancedCourse->id,
                'title' => '教材ダウンロード',
                'type' => 'download',
                'is_published' => true,
                'published_at' => now(),
                'sort_order' => 1,
            ]
        );

        PageContent::firstOrCreate(
            ['page_id' => $dlPage->id, 'sort_order' => 1],
            [
                'type' => 'text',
                'body' => '<p>上級コースの教材をダウンロードできます。</p>',
            ]
        );

        PageContent::firstOrCreate(
            ['page_id' => $dlPage->id, 'sort_order' => 2],
            [
                'type' => 'download',
                'download_id' => $download->id,
            ]
        );

        // --- 登録フォーム ---
        if ($freePlan) {
            RegistrationForm::firstOrCreate(
                ['slug' => 'free-register'],
                [
                    'name' => '無料会員登録',
                    'type' => 'free',
                    'plan_id' => $freePlan->id,
                    'description' => '無料でコンテンツにアクセスできます。',
                    'button_text' => '無料で登録する',
                    'thanks_message' => 'ご登録ありがとうございます！メールをご確認ください。',
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );
        }

        if ($standardPlan) {
            RegistrationForm::firstOrCreate(
                ['slug' => 'standard-register'],
                [
                    'name' => 'スタンダードプラン登録',
                    'type' => 'paid_recurring',
                    'plan_id' => $standardPlan->id,
                    'description' => '月額2,980円で基礎コースにアクセスできます。',
                    'button_text' => 'スタンダードプランに申し込む',
                    'amount' => 2980,
                    'payment_gateway' => 'none',
                    'thanks_message' => 'お申し込みありがとうございます！',
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );
        }

        // --- メニュー ---
        MenuItem::firstOrCreate(
            ['label' => 'ダッシュボード', 'sort_order' => 1],
            [
                'type' => 'url',
                'url' => '/mypage',
                'icon' => 'heroicon-o-home',
                'is_visible' => true,
            ]
        );

        MenuItem::firstOrCreate(
            ['label' => $gettingStarted->name, 'sort_order' => 2],
            [
                'type' => 'category',
                'target_id' => $gettingStarted->id,
                'icon' => 'heroicon-o-book-open',
                'is_visible' => true,
            ]
        );

        MenuItem::firstOrCreate(
            ['label' => $basicCourse->name, 'sort_order' => 3],
            [
                'type' => 'category',
                'target_id' => $basicCourse->id,
                'icon' => 'heroicon-o-academic-cap',
                'is_visible' => true,
            ]
        );

        MenuItem::firstOrCreate(
            ['label' => $advancedCourse->name, 'sort_order' => 4],
            [
                'type' => 'category',
                'target_id' => $advancedCourse->id,
                'icon' => 'heroicon-o-star',
                'is_visible' => true,
            ]
        );
    }
}
