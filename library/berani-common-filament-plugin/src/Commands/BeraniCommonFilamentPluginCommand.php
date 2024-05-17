<?php

namespace BeraniDigitalID\BeraniCommonFilamentPlugin\Commands;

use Illuminate\Console\Command;

class BeraniCommonFilamentPluginCommand extends Command
{
    public $signature = 'berani-common-filament-plugin';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
