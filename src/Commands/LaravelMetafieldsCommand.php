<?php

namespace FaizanSf\LaravelMetafields\Commands;

use Illuminate\Console\Command;

class LaravelMetafieldsCommand extends Command
{
    public $signature = 'laravel-metafields';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
