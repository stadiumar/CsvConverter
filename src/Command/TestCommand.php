<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use App\Services\ParseCsvService;
use App\Services\TblProductDataService;

class TestCommand extends Command
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
            ->setName('test-command')
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
   
        if ($this->parseCsvService->fileIsTooBig(filesize($targetFilePath))) {
            $output->writeln('File is too big. Please use smaller file.');
            
            return 0;
        }
    
        $tempFilePath = './temp/temp.csv';
        
        // if there is any line termination problem, it just considered as null value
        // solution of potential problem with empty lines
        $this->parseCsvService->eliminateEmptyStrings($targetFilePath, $tempFilePath); 
        $targetFilePath = $tempFilePath;

        if (
            mime_content_type($targetFilePath) != 'text/csv' 
            || pathinfo($targetFilePath, PATHINFO_EXTENSION) != 'csv'
        ) {
            $output->writeln('File is not CSV. Please use CSV files only.');

            return 0;
        }

        $lines = $this->parseCsvService->getLines($targetFilePath);
        
        $fieldNames = array_shift($lines);

        $data = $this->parseCsvService->getFullData($lines, $fieldNames);

        $importResult = $this->productService->massImportProduct($data, $input->getArgument('executionMode'));

        if ($importResult['status'] != 'success') {
            $output->writeln($importResult['status']);

            return 0;
        }
 
        $skipped = $importResult['skippedRecords'];

        $output->writeln('Items skipped (not to be imported): ' . count($skipped));

        foreach($skipped as $skippedItem) {
            $output->writeln($skippedItem);
        }

        $output->writeln("\nItems processed: ". count($data));

        if ($input->getArgument('executionMode') != 'test') {
            $output->writeln('Items successfully imported: ' . count($data) - count($skipped));
        }
        
        unlink($tempFilePath);

        return 0;
    }
}