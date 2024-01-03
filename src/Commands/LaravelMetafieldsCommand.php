<?php

namespace FaizanSf\LaravelMetafields\Commands;

use Illuminate\Console\Command;

class LaravelMetafieldsCommand extends Command
{
    public $signature = 'make:metafield';

    public $description = 'Create a metafield class';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
