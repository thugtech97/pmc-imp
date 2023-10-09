<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;

class ActivateAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post announcements on a certain date.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $announcements = Announcement::whereNotNull('schedule')->where('status', 'disabled')->get();

        foreach ($announcements as $announcement) {
            if (date('Y-m-d') == $announcement->schedule) Announcement::find($announcement->id)->update(['status' => 'active']);
        }
    }
}
