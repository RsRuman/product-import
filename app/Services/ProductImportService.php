<?php

namespace App\Services;

use AllowDynamicProperties;
use App\Interfaces\ProductInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;

#[AllowDynamicProperties]
class ProductImportService
{
    public function __construct(ProductInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Importing stock csv file data
     * @param $filePath
     * @param $isTestMode
     * @return array
     * @throws Exception
     */
    public function import($filePath, $isTestMode): array
    {
        // Checking file exist or not
        if (!Storage::exists($filePath)) {
            throw new Exception("File not found");
        }

        $file = fopen(Storage::path($filePath), 'r');

        if (!$file) {
            throw new Exception("Failed to open file: $filePath");
        }

        // Skip the header row
        $header = fgetcsv($file);
        if (!$header) {
            throw new Exception("Failed to read CSV header");
        }

        $processed  = 0;
        $successful = 0;
        $skipped    = 0;
        $failed     = [];

        while ($row = fgetcsv($file)) {
            $processed++;

            $data = [
                'Product Code'        => $row[0],
                'Product Name'        => $row[1],
                'Product Description' => $row[2],
                'Stock'               => $row[3] ?? 0,
                'Cost in GBP'         => $row[4] ?? 0,
                'Discontinued'        => isset($row[5]) && strtolower($row[5]) === 'yes' ? Carbon::now() : null
            ];

            // Business rules
            if ($data['Cost in GBP'] < 5 || $data['Cost in GBP'] > 1000) {
                $skipped++;
                continue;
            }

            if ($data['Stock'] < 10) {
                $skipped++;
                continue;
            }

            try {
                if (!$isTestMode) {
                    $this->productRepository->insertProduct([
                        'name'        => $data['Product Name'],
                        'desc'        => $data['Product Description'],
                        'code'        => $data['Product Code'],
                        'added'       => Carbon::now(),
                        'discontinue' => $data['Discontinued'],
                        'stock_level' => $data['Stock'],
                        'price'       => $data['Cost in GBP'],
                    ]);
                }

                $successful++;
            } catch (Exception $e) {
                $failed[] = $data['Product Name'];
            }
        }

        fclose($file);

        return [
            'processed' => $processed,
            'successful' => $successful,
            'skipped' => $skipped,
            'failed' => $failed,
        ];
    }
}
