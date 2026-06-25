<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\Notification;

class SubscribeToRedis extends Command
{
    protected $signature = 'redis:subscribe';
    protected $description = 'Subscribe to Redis order_created channel';

    public function handle()
    {
        $this->info('Subscribed to order_created channel...');
        
        Redis::subscribe(['order_created'], function ($message) {
            $data = json_decode($message, true);
            
            $this->info("Received notification for User ID: " . $data['user_id']);
            
            Notification::create([
                'user_id' => $data['user_id'],
                'message' => $data['message'],
                'is_read' => false
            ]);
        });
    }
}
