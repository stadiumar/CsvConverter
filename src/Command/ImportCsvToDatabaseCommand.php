<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use App\Services\ParseCsvService;
use App\Services\TblProductDataService;

class ImportCsvToDatabaseCommand extends Command
{
    private $parseCsvService;

    private $productService;

    public function __construct(
        ParseCsvService $parseCsvService,
        TblProductDataService $productService
    ) {
        $this->parseCsvService = $parseCsvService;
        $this->productService = $productService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('import-csv-to-database-command')
            ->addArgument('newFilePath', null, InputOption::VALUE_REQUIRED)
            ->addArgument('executionMode', null, InputOption::VALUE_OPTIONAL, 'dev')
            ;
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $targetFilePath = $input->getArgument('newFilePath');

        if (!file_exists($targetFilePath)) {
            $output->writeln('File not found. Please check path.');

            return 0;
        }
   
        // set file size limit for command to 3 GB
        if ($this->parseCsvService->fileIsTooBig(filesize($targetFilePath))) {
            $output->writeln('File is too big. Please use smaller file.');
            
            return 0;
        }
    
        $tempFilePath = './temp/temp.csv';
        
        // solution of potential problem with empty lines and wrong delimiters
        // we need to do it before check of extension, because empty line in the begging will make text/plain from csv
        $this->parseCsvService->eliminateEmptyStrings($targetFilePath, $tempFilePath); 
        // make temporary file with formatted content as target
        $targetFilePath = $tempFilePath;

        if (
            mime_content_type($targetFilePath) != 'text/csv' 
            || pathinfo($targetFilePath, PATHINFO_EXTENSION) != 'csv'
        ) {
            $output->writeln('File is not CSV. Please use CSV files only.');

            return 0;
        }

        // In order to check whether the data is correctly formatted in CSV i check every item and 
        // get lines as arrays from file
        $lines = $this->parseCsvService->getLines($targetFilePath);
        
        // get the first line as field names and remove it from the array
        $fieldNames = array_shift($lines);

        // get mulitydemencional array of fields and values from lines 
        $fieldsWithValues = $this->parseCsvService->getFullData($lines, $fieldNames);

        // process fields and values to create product objects and save to database
        $importResult = $this->productService->massImportProduct($fieldsWithValues, $input->getArgument('executionMode'));

        // if import is not successful, print error message
        if ($importResult['status'] != 'success') {
            $output->writeln($importResult['status']);

            return 0;
        }
 
        $skipped = $importResult['skippedRecords'];

        $output->writeln('Items skipped (not to be imported): ' . count($skipped));

        foreach($skipped as $skippedItem) {
            $output->writeln($skippedItem);
        }

        $output->writeln("\nItems processed: ". count($fieldsWithValues));

        if ($input->getArgument('executionMode') != 'test') {
            $output->writeln('Items successfully imported: ' . count($fieldsWithValues) - count($skipped));
        }
        
        // delete temporary file
        unlink($tempFilePath);

        return 0;
    }
}