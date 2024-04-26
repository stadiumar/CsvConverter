<?php

namespace App\Services;


use App\Entity\TblProductData;
use App\Tests\DatabaseDependantTestCase;
use App\Services\TblProductDataService;
use App\Services\CurrencyService;
use DateTime;

class TblProductDataServiceTest extends DatabaseDependantTestCase
{
    private $productService;

    private $currencyService;

    private $testData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testData = [
            0 => [
                "ProductCode" => "P0028",
                "ProductName" => "Bluray Player",
                "ProductDescription" => "Plays bluray's",
                "Stock" => "32",
                "CostInGbp" => "110.04",
                "Discontinued" => "yes",
            ]
        ];

        
        $this->currencyService = new CurrencyService();
        $this->productService = new TblProductDataService($this->entityManager, $this->currencyService);
    }

    public function testProductRecordCanBeCreatedInDatabase()
    {
        $product = new TblProductData();

        $product->setStrProductName('test');
        $product->setStrProductDesc('description');
        $product->setStrProductCode('code');
        
        $this->entityManager->persist($product);

        $this->entityManager->flush();

        $productRepository = $this->entityManager->getRepository(TblProductData::class);
        $productRecord = $productRepository->findOneByStrProductCode('code');

        $this->assertEquals('test', $productRecord->getStrProductName());
        $this->assertEquals('description', $productRecord->getStrProductDesc());
        $this->assertEquals('code', $productRecord->getStrProductCode());
    }

    public function testProductIsCreatedFromImportData()
    {
        $result = $this->productService->massImportProduct($this->testData);

        $productRepository = $this->entityManager->getRepository(TblProductData::class);
        $productRecord = $productRepository->findOneByStrProductCode('P0028');
        
        $this->assertInstanceOf(TblProductData::class, $productRecord);
        $this->assertEquals('P0028', $productRecord->getStrProductCode());
        $this->assertEquals('Bluray Player', $productRecord->getStrProductName());
        $this->assertEquals('Plays bluray\'s', $productRecord->getStrProductDesc());
        $this->assertEquals('32', $productRecord->getStock());
        $this->assertEquals(110.04, $productRecord->getCostGbp());

        $this->assertSame(['status' => 'success', 'skipped' => 0], $result);

    }

    public function testDiscontinuedIsSetToCurrentDateWhenImport()
    {
        $this->productService->massImportProduct($this->testData);

        $productRepository = $this->entityManager->getRepository(TblProductData::class);

        $productRecord = $productRepository->findOneByStrProductCode('P0028');

        $this->assertNotEquals('yes', $productRecord->getDtmDiscontinued());
        $this->assertEquals('2024-04-25', $productRecord->getDtmDiscontinued()->format('Y-m-d'));
    }

    public function testRecordOver1000isSkipped()
    {
        $testData = [
            0 => [
                "ProductCode" => "P0028",
                "ProductName" => "Bluray Player",
                "ProductDescription" => "Plays bluray's",
                "Stock" => "32",
                "CostInGbp" => "1100.04",
                "Discontinued" => "yes",
            ]
        ];

        $this->productService->massImportProduct($testData);

        $productRepository = $this->entityManager->getRepository(TblProductData::class);

        $productRecord = $productRepository->findOneByStrProductCode($testData[0]["ProductCode"]);

        $this->assertEquals(null, $productRecord);
    }

    public function testFlagPreventsSavingOfTheRecord()
    {
        $this->productService->massImportProduct($this->testData, 'test');
        $productRepository = $this->entityManager->getRepository(TblProductData::class);

        $productRecord = $productRepository->findOneByStrProductCode($this->testData[0]["ProductCode"]);

        $this->assertEquals(null, $productRecord);
    }

    public function testEmptyData()
    {
        $this->productService->massImportProduct([]);
        $productRepository = $this->entityManager->getRepository(TblProductData::class);
        $productRecord = $productRepository->findOneByStrProductCode($this->testData[0]["ProductCode"]);

        $this->assertEquals(null, $productRecord);
    }

    public function testRecordLessThan5inCostAndLessThan10InStockIsSkipped()
    {
        $testData = [
            0 => [
                "ProductCode" => "P0028",
                "ProductName" => "Bluray Player",
                "ProductDescription" => "Plays bluray's",
                "Stock" => "9",
                "CostInGbp" => "2",
                "Discontinued" => "yes",
            ]
        ];

        $this->productService->massImportProduct($testData);
        $productRepository = $this->entityManager->getRepository(TblProductData::class);
        $productRecord = $productRepository->findOneByStrProductCode($testData[0]["ProductCode"]);

        $this->assertEquals(null, $productRecord);
    }
}