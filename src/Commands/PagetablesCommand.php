<?php

namespace Savannabits\Pagetables\Commands;

use Illuminate\Console\Command;

class PagetablesCommand extends Command
{
    public $signature = 'laravel-pagetables';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
