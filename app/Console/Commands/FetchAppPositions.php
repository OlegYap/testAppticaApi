<?php

namespace App\Console\Commands;

use App\Services\AppticaService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FetchAppPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-positions';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch app positions from Apptica API';
    /**
     * Execute the console command.
     */

    public function __construct(
        private AppticaService $appticaService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $dateFrom = Carbon::now()->subDays(2)->format('Y-m-d');
            $dateTo = Carbon::now()->format('Y-m-d');

            $this->info("Fetching data from $dateFrom to $dateTo");

            $data = $this->appticaService->fetchData($dateFrom, $dateTo);
            $this->appticaService->savePositions($data);

            $this->info('Data successfully updated');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
