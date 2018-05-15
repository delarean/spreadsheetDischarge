<?php

namespace App\Console\Commands;

use App\Traits\MySkladReportTrait;
use Illuminate\Console\Command;

class DownloadImages extends Command
{
    use MySkladReportTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:images {limit=1500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->downloadImages($this->argument('limit'));
    }
}
