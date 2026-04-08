<?php

namespace Foxws\ScoutBuilder\Commands;

use Illuminate\Console\Command;

class ScoutBuilderCommand extends Command
{
    public $signature = 'laravel-scout-builder';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
