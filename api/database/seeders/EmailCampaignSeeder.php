<?php

namespace Database\Seeders;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmailCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(1)->get();
        $userId = $users->first()?->id;

        if (!$userId) {
            $this->command->warn('No users found. Skipping EmailCampaignSeeder.');
            return;
        }

        $campaigns = [
            [
                'name' => 'Summer Sale 2024',
                'subject' => 'Hot Summer Deals - Up to 50% Off!',
                'content' => '<h1>Summer Sale!</h1><p>Don\'t miss our hottest deals of the season. Up to 50% off on selected items.</p>',
                'status' => 'sent',
                'scheduled_at' => null,
                'sent_at' => now()->subDays(5),
                'created_by' => $userId,
            ],
            [
                'name' => 'New Product Launch',
                'subject' => 'Check Out Our Latest Arrivals!',
                'content' => '<h1>New Products Available</h1><p>We just added amazing new products to our store. Shop now!</p>',
                'status' => 'draft',
                'scheduled_at' => null,
                'sent_at' => null,
                'created_by' => $userId,
            ],
            [
                'name' => 'Weekend Flash Sale',
                'subject' => 'Flash Sale This Weekend Only!',
                'content' => '<h1>Flash Sale!</h1><p>48 hours only. Grab the best deals before they\'re gone!</p>',
                'status' => 'scheduled',
                'scheduled_at' => now()->addDays(2),
                'sent_at' => null,
                'created_by' => $userId,
            ],
            [
                'name' => 'Black Friday Preview',
                'subject' => 'Black Friday Coming Soon',
                'content' => '<h1>Black Friday Deals</h1><p>Prepare for the biggest sale of the year. Sign up now to get early access!</p>',
                'status' => 'draft',
                'scheduled_at' => null,
                'sent_at' => null,
                'created_by' => $userId,
            ],
            [
                'name' => 'Holiday Thanks',
                'subject' => 'Thank You for Being a Customer',
                'content' => '<h1>Thank You!</h1><p>We appreciate your continued support. Here\'s a special discount just for you!</p>',
                'status' => 'sent',
                'scheduled_at' => null,
                'sent_at' => now()->subDays(10),
                'created_by' => $userId,
            ],
        ];

        foreach ($campaigns as $campaignData) {
            EmailCampaign::create($campaignData);
        }
    }
}