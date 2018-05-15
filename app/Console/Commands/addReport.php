<?php

namespace App\Console\Commands;

use App\Traits\MySkladReportTrait;
use Illuminate\Console\Command;

class addReport extends Command
{

    use MySkladReportTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make mysklad report';

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
     * @return mixed
     */
    public function handle()
    {
        $this->newReport();
    }
}
