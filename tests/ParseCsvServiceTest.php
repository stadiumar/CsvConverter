<?php 

namespace App\Tests;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use org\bovigo\vfs\vfsStream;
use App\Services\ParseCsvService;

class ParseCsvServiceTest extends KernelTestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    /**
     * @var  ParseCsvService
     */
    private $parseCsvService;

    protected function setUp(): void
    {
        $directory = [
            'csv' => [
                'input.csv' => "first1,first2,first3\nsecond1,second2,second3\nthird1,third2,third3\n\n",
                'empty.csv' => "",
                'strange_encoding.csv' => "first1,first2,first3\r\nsecond1,second2,second3\r\nthird1,third2,third3",
            ]
        ];
    
        $this->root = vfsStream::setup('root', 444, $directory);
        $this->parseCsvService = new ParseCsvService();
    }

    public function testGetExpectedEmountOfLines() {
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/input.csv');

        $this->assertCount(3, $lines);

        $this->assertCount(3, $lines[0]);
        $this->assertCount(3, $lines[1]);
        $this->assertCount(3, $lines[2]);

        $this->assertEquals('second1', $lines[1][0]);
    }

    public function testEmptyFile() {
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/empty.csv');

        $this->assertCount(0, $lines);
    }

    public function testNonExistingFile() {
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/not_exists.csv');

        $this->assertCount(0, $lines);
    }

    public function testEmptyLines() {
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/input.csv');

        $fieldNames = ['first1', 'first2', 'first3'];
        $fullData = $this->parseCsvService->getFullData($lines, $fieldNames);
        
        $this->assertCount(3, $fullData);
    }

    public function testFirstLineIsEmpty() {
        
    }
}