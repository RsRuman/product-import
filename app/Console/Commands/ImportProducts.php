<?php

namespace App\Console\Commands;

use AllowDynamicProperties;
use App\Services\ProductImportService;
use Exception;
use Illuminate\Console\Command;

#[AllowDynamicProperties]
class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products {file} {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from csv file';

    public function __construct(ProductImportService  $productImportService)
    {
        parent::__construct();
        $this->productImportService = $productImportService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $filePath   = $this->argument('file');
        $isTestMode = $this->option('test');

        try {
            $result = $this->productImportService->import($filePath, $isTestMode);

            $this->info("Processed: {$result['processed']}");
            $this->info("Successful: {$result['successful']}");
            $this->info("Skipped: {$result['skipped']}");
            $this->info("Failed: " . count($result['failed']));

            if (count($result['failed']) > 0) {
                $this->info("Failed entries: " . implode(', ', $result['failed']));
            }

            $this->info('Import process completed.');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
