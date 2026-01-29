<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'allow_registration',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Cho phép người dùng mới đăng ký tài khoản.',
            ],
            [
                'key' => 'require_email_verification',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Yêu cầu xác nhận email trước khi đăng nhập.',
            ],
            [
                'key' => 'default_user_role',
                'value' => 'user',
                'type' => 'string',
                'description' => 'Vai trò mặc định cho người dùng mới (user, guest, admin).',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Bật chế độ bảo trì hệ thống (chỉ admin truy cập được).',
            ],
            [
                'key' => 'enable_ai_chat',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Bật/Tắt tính năng AI Chat.',
            ],
            [
                'key' => 'default_language',
                'value' => 'vi',
                'type' => 'string',
                'description' => 'Ngôn ngữ mặc định của hệ thống.',
            ],
            [
                'key' => 'gmail_search_query',
                'value' => 'subject:(biến động số dư) OR subject:(giao dịch) OR subject:(transaction) OR subject:(biên lai) OR subject:(transfer) OR subject:(receipt) -category:promotions -category:social',
                'type' => 'string',
                'description' => 'Truy vấn tìm kiếm email ngân hàng (Gmail).',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
