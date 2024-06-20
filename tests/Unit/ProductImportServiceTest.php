<?php

namespace Tests\Unit;

use App\Interfaces\ProductInterface;
use App\Services\ProductImportService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\TestCase;

class ProductImportServiceTest extends TestCase
{
    protected $productRepository;
    protected $productImportService;

    public function setUp(): void
    {
        parent::setUp();

        // Mock the ProductInterface
        $this->productRepository    = Mockery::mock(ProductInterface::class);
        $this->productImportService = new ProductImportService($this->productRepository);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    /**
     * Success test
     * @throws Exception
     */
    public function test_import_successful()
    {
        // Create a mock CSV file
        $csvContent = <<<CSV
            Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued
            123,Test Product,Description,20,50,no
            124,Test Product 2,Description 2,5,3,no
            125,Test Product 3,Description 3,30,2000,no
            126,Discontinued Product,Description 4,15,150,yes
            CSV;

        $filePath = '/csv/test_file.csv';

        file_put_contents('storage/app' . $filePath, $csvContent);

        $fullPath = __DIR__ . '/../../storage/app/' . $filePath;

        // Simulate Storage::exists returning true
        Storage::shouldReceive('exists')->with($filePath)->andReturn(true);

        // Simulate Storage::path returning the full path
        Storage::shouldReceive('path')->with($filePath)->andReturn($fullPath);


        $result = $this->productImportService->import($filePath, false);

        unlink('storage/app/csv/test_file.csv');

        $this->assertEquals(4, $result['processed']);
        $this->assertEquals(0, $result['successful']);
        $this->assertEquals(2, $result['skipped']);
        $this->assertEquals('Test Product', $result['failed'][0]);
        $this->assertEquals('Discontinued Product', $result['failed'][1]);
    }

    /**
     * Failed test
     * @return void
     * @throws Exception
     */
    public function test_import_file_not_found()
    {
        $filePath = '/csv/non_existent_file.csv';

        // Simulate Storage::exists returning false
        Storage::shouldReceive('exists')->with($filePath)->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File not found');

        $this->productImportService->import($filePath, false);
    }
}
