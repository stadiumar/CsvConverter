<?php

namespace App\Services;
use App\Entity\TblProductData;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;

class TblProductDataService
{
    const TEST_MODE = 'test';
    const YES = 'yes';
    const REQUIERED_FIELDS = ['ProductCode', 'ProductName', 'ProductDescription'];

    private $entityManager;

    private $currencyService;

    private $registry;

    public function __construct(
        EntityManagerInterface $entityManager,
        CurrencyService $currencyService,
        ManagerRegistry $registry
    ) {
        $this->entityManager = $entityManager;
        $this->currencyService = $currencyService;
        $this->registry = $registry;
    }

    /**
     * Imports a batch of products into the database.
     *
     * @param array $data An array of product data. Each item in the array must have the following keys:
     *                    - ProductCode: a string representing the product code.
     *                    - ProductName: a string representing the product name.
     *                    - ProductDescription: a string representing the product description.
     * @param string $env The environment in which the import is being done. Defaults to 'dev'.
     * @return array An array with the following keys:
     *               - status: a string indicating the status of the import. Can be 'success' or an error message.
     *               - skippedRecords: an array of strings representing the product codes and names that were skipped due to invalid data or cost constraints.
     * @throws \Exception If an error occurs during the import process.
     */
    public function massImportProduct(array $data, $env = 'dev'): array
    {
        $skippedRecords = [];
        $status = 'success';

        // $this->entityManager->beginTransaction();
        // not to save product if one of reqired fields is missing ?
        // move transaction to the single product creation because if one of the required fields is missing the whole butch will be rejected

        foreach($data as $item) {
            foreach(self::REQUIERED_FIELDS as $requiredField) {
                if (!array_key_exists($requiredField, $item)) continue 2;
            }

            $product = new TblProductData();
            // i would make function to substr field to acceptable sized to database
            $product->setStrProductCode(substr($item['ProductCode'], 0, 10));    
            $product->setStrProductName(substr($item['ProductName'], 0, 50));
            $product->setStrProductDesc(substr($item['ProductDescription'], 0, 255));

            if (
                array_key_exists('Stock', $item) 
                && preg_match('/^\d+$/', $item['Stock'])
                ) {
                $product->setStock((int)$item['Stock']);
            }

            if (
                array_key_exists('CostInGbp', $item)
                && is_numeric($item['CostInGbp'])
                ) {
                $product->setCostGbp((float)$item['CostInGbp']);
            }

            if (
                array_key_exists('Discontinued', $item)
                && $item['Discontinued'] == self::YES
                ) {
                $product->setDtmDiscontinued(new DateTime());
            }
        
            if ($costGbp = $product->getCostGbp()) {
                $costInUSD = $this->currencyService
                                ->getConvertedAmmount($costGbp, $this->currencyService::GBP, $this->currencyService::USD);
    
                if ($costInUSD && ($costInUSD > 1000 || ($costInUSD < 5 && (int)$product->getStock() < 10))) {
                    $skippedRecords[] = $product->getStrProductCode() . ' - ' . $product->getStrProductName();

                    continue;
                }
            }

            try {
                $this->entityManager->beginTransaction();
                $this->entityManager->persist($product);

                if ($env != self::TEST_MODE) {
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                }

            } catch (\Exception $e) {
                $this->entityManager->rollback();
                // $this->registry->resetManager();

                $this->entityManager->flush();
                $this->entityManager->commit();
                $status = $e->getMessage();
            }
        }

        return ['status' => $status, 'skippedRecords' => $skippedRecords];
    }
}