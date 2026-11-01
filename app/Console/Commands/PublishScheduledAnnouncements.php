<?php

namespace App\Console\Commands;

use App\Services\AnnouncementService;
use Illuminate\Console\Command;

class PublishScheduledAnnouncements extends Command
{
    protected $signature = 'announcements:publish-scheduled';

    protected $description = 'Dispatch announcements whose scheduled time has arrived';

    public function handle(AnnouncementService $announcements): int
    {
        $count = $announcements->publishDue();
        $this->info("Dispatched {$count} scheduled announcement(s).");
        return self::SUCCESS;
    }
}
