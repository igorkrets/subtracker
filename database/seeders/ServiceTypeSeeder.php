<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['slug' => 'vps', 'name' => 'VPS / VDS', 'name_ru' => 'VPS / VDS', 'icon' => 'server', 'color' => '#2563EB', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7,14,30], 'sort_order' => 1],
            ['slug' => 'dedicated', 'name' => 'Dedicated Server', 'name_ru' => 'Выделенный сервер', 'icon' => 'server-cog', 'color' => '#1E40AF', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7,14,30], 'sort_order' => 2],
            ['slug' => 'hosting', 'name' => 'Hosting', 'name_ru' => 'Хостинг', 'icon' => 'hard-drive', 'color' => '#0891B2', 'default_billing_cycle' => 'yearly', 'default_notify_days' => [7,14,30], 'sort_order' => 3],
            ['slug' => 'domain', 'name' => 'Domain', 'name_ru' => 'Домен', 'icon' => 'globe', 'color' => '#16A34A', 'default_billing_cycle' => 'yearly', 'default_notify_days' => [7,14,30,60], 'sort_order' => 4],
            ['slug' => 'ssl', 'name' => 'SSL Certificate', 'name_ru' => 'SSL-сертификат', 'icon' => 'shield-check', 'color' => '#15803D', 'default_billing_cycle' => 'yearly', 'default_notify_days' => [7,14,30], 'sort_order' => 5],
            ['slug' => 'cloud', 'name' => 'Cloud', 'name_ru' => 'Облако', 'icon' => 'cloud', 'color' => '#0EA5E9', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7,14], 'sort_order' => 6],
            ['slug' => 'cdn', 'name' => 'CDN', 'name_ru' => 'CDN', 'icon' => 'network', 'color' => '#F97316', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 7],
            ['slug' => 'storage', 'name' => 'Storage', 'name_ru' => 'Хранилище', 'icon' => 'database', 'color' => '#6366F1', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 8],
            ['slug' => 'backup', 'name' => 'Backup', 'name_ru' => 'Бэкап', 'icon' => 'archive', 'color' => '#64748B', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 9],
            ['slug' => 'email', 'name' => 'Email', 'name_ru' => 'Почта', 'icon' => 'mail', 'color' => '#0284C7', 'default_billing_cycle' => 'yearly', 'default_notify_days' => [7,14,30], 'sort_order' => 10],
            ['slug' => 'database_service', 'name' => 'Database (DBaaS)', 'name_ru' => 'База данных (DBaaS)', 'icon' => 'database', 'color' => '#4F46E5', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7,14], 'sort_order' => 11],
            ['slug' => 'vpn', 'name' => 'VPN', 'name_ru' => 'VPN', 'icon' => 'shield', 'color' => '#7C3AED', 'default_billing_cycle' => 'yearly', 'default_notify_days' => [7,14,30], 'sort_order' => 12],
            ['slug' => 'proxy', 'name' => 'Proxy', 'name_ru' => 'Прокси', 'icon' => 'waypoints', 'color' => '#8B5CF6', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 13],
            ['slug' => 'monitoring', 'name' => 'Monitoring', 'name_ru' => 'Мониторинг', 'icon' => 'activity', 'color' => '#DC2626', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 14],
            ['slug' => 'security', 'name' => 'Security', 'name_ru' => 'Безопасность', 'icon' => 'lock', 'color' => '#B91C1C', 'default_billing_cycle' => 'yearly', 'default_notify_days' => [7,14,30], 'sort_order' => 15],
            ['slug' => 'saas', 'name' => 'SaaS', 'name_ru' => 'SaaS-подписка', 'icon' => 'box', 'color' => '#6D28D9', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7,14], 'sort_order' => 16],
            ['slug' => 'streaming', 'name' => 'Streaming', 'name_ru' => 'Стриминг / медиа', 'icon' => 'clapperboard', 'color' => '#E11D48', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7], 'sort_order' => 17],
            ['slug' => 'music', 'name' => 'Music', 'name_ru' => 'Музыка', 'icon' => 'music', 'color' => '#DB2777', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7], 'sort_order' => 18],
            ['slug' => 'design', 'name' => 'Design', 'name_ru' => 'Дизайн', 'icon' => 'palette', 'color' => '#EA580C', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 19],
            ['slug' => 'dev_tool', 'name' => 'Dev Tool', 'name_ru' => 'Инструмент разработки', 'icon' => 'code', 'color' => '#0F766E', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 20],
            ['slug' => 'ai', 'name' => 'AI Service', 'name_ru' => 'AI-сервис', 'icon' => 'sparkles', 'color' => '#9333EA', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7], 'sort_order' => 21],
            ['slug' => 'analytics', 'name' => 'Analytics', 'name_ru' => 'Аналитика', 'icon' => 'bar-chart-3', 'color' => '#CA8A04', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 22],
            ['slug' => 'communication', 'name' => 'Communication', 'name_ru' => 'Коммуникации', 'icon' => 'message-circle', 'color' => '#2563EB', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7], 'sort_order' => 23],
            ['slug' => 'license', 'name' => 'License', 'name_ru' => 'Лицензия ПО', 'icon' => 'key', 'color' => '#475569', 'default_billing_cycle' => 'yearly', 'default_notify_days' => [7,14,30], 'sort_order' => 24],
            ['slug' => 'game', 'name' => 'Gaming', 'name_ru' => 'Игры / игровые сервисы', 'icon' => 'gamepad-2', 'color' => '#059669', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7], 'sort_order' => 25],
            ['slug' => 'payment', 'name' => 'Payment', 'name_ru' => 'Платёжный сервис', 'icon' => 'credit-card', 'color' => '#0D9488', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [3,7,14], 'sort_order' => 26],
            ['slug' => 'other', 'name' => 'Other', 'name_ru' => 'Другое', 'icon' => 'tag', 'color' => '#6B7280', 'default_billing_cycle' => 'monthly', 'default_notify_days' => [1,3,7,14,30], 'sort_order' => 27],
        ];

        foreach ($types as $type) {
            ServiceType::updateOrCreate(
                ['slug' => $type['slug']],
                array_merge($type, ['default_notify_days' => json_encode($type['default_notify_days'])])
            );
        }
    }
}
