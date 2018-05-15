<?php

namespace App\Console\Commands;

use App\Traits\MySkladReportTrait;
use Illuminate\Console\Command;

class deleteReport extends Command
{
    use MySkladReportTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete previous report from DB';

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
        $this->deletePreviousReport();
    }
}
