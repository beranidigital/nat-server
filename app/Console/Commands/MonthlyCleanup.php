<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Pool\StateLog;
use App\Models\State;
use Illuminate\Console\Command;

class MonthlyCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monthly-cleanup {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the monthly cleanup tasks';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting the monthly cleanup...');

        if ($dryRun) {
            $this->info('Dry run enabled. No changes will be made.');
        }
        // only keep x month worth of data
        $maxMonths = 2;
        $inMilliseconds = now()->subMonths($maxMonths)->timestamp * 1000;

        $totalEventsTableDeleted = Event::where('time_fired_ts', '<', $inMilliseconds)->count();
        $totalEventsTable = Event::count();
        $totalEventsTableDeleted = number_format($totalEventsTableDeleted);
        $totalEventsTable = number_format($totalEventsTable);
        //$this->info("Deleting $totalEventsTableDeleted out of $totalEventsTable records from the events table...");
        if (!$dryRun) {
            Event::where('time_fired_ts', '<', $inMilliseconds)->delete();
        }

        $totalStatesTableDeleted = State::where('last_updated_ts', '<', $inMilliseconds)->count();
        $totalStatesTable = State::count();
        $totalStatesTableDeleted = number_format($totalStatesTableDeleted);
        $totalStatesTable = number_format($totalStatesTable);
        $this->info("Deleting $totalStatesTableDeleted out of $totalStatesTable records from the states table...");
        if (!$dryRun) {
            // doesnt work because it reference itself lol
            //State::where('last_updated_ts', '<', $inMilliseconds)->delete();
        }

        $totalStateLogsTableDeleted = StateLog::where('created_at', '<', now()->subMonths($maxMonths * 6))->count();
        $totalStateLogsTable = StateLog::count();
        $totalStateLogsTableDeleted = number_format($totalStateLogsTableDeleted);
        $totalStateLogsTable = number_format($totalStateLogsTable);
        $this->info("Deleting $totalStateLogsTableDeleted out of $totalStateLogsTable records from the state_logs table...");
        if (!$dryRun) {
            StateLog::where('created_at', '<', now()->subMonths($maxMonths * 6))->delete();
        }

        $this->info('Monthly cleanup completed.');

    }
}
