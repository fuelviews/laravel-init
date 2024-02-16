<?php

namespace Fuelviews\LaravelInit\Commands;

use Illuminate\Console\Command;

class LaravelInitCommand extends Command
{
    public $signature = 'laravel-init';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
