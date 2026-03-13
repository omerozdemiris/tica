<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class SendCartReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-cart-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send cart reminder notifications to users with abandoned carts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        NotificationService::processAllCartReminders();
        $this->info('Cart reminders processed successfully.');
    }
}
