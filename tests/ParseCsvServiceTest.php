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
                'input.csv' => "first1,first2,first3\nsecond1,second2,second3\nthird1,third2,third3",
                'input2.csv' => "first1,first2,first3\nsecond1,second2,second3\nthird1,third2,third3\n\n",
                'first_empty.csv' => "\nfirst1,first2,first3\nsecond1,second2,second3\nthird1,third2,third3",
                'check_delimeters.csv' => "\r\nfirst1,first2,first3\r\nsecond1,second2,second3\r\nthird1,third2,third3",
                'temp.csv' => "",
            ]
        ];
    
        $this->root = vfsStream::setup('root', 444, $directory);
        $this->parseCsvService = new ParseCsvService();
    }

    /** @test */
    public function testGetExpectedEmountOfLines() {
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/input.csv');

        $this->assertCount(3, $lines);

        $this->assertCount(3, $lines[0]);
        $this->assertCount(3, $lines[1]);
        $this->assertCount(3, $lines[2]);

        $this->assertEquals('second1', $lines[1][0]);
    }

    /** @test */
    public function testEmptyFile() {
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/empty.csv');

        $this->assertCount(0, $lines);
    }

    /** @test */
    public function testNonExistingFile() {
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/not_exists.csv');

        $this->assertCount(0, $lines);
    }

    /** @test */
    public function testEmptyLines() {
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/input2.csv');

        $fieldNames = ['first1', 'first2', 'first3'];
        $fullData = $this->parseCsvService->getFullData($lines, $fieldNames);
        
        $this->assertCount(3, $fullData);
    }

    /** @test */
    public function testEliminateEmptyStrings() {
        $this->parseCsvService->eliminateEmptyStrings($this->root->url().'/csv/first_empty.csv', $this->root->url().'/csv/temp.csv');
       
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/temp.csv');
        $this->assertCount(3, $lines);
    }

     /** @test */
    public function testDelimetersStrings() {
        $this->parseCsvService->eliminateEmptyStrings($this->root->url().'/csv/check_delimeters.csv', $this->root->url().'/csv/temp1.csv');
       
        $lines = $this->parseCsvService->getLines($this->root->url().'/csv/temp1.csv');
        $this->assertCount(3, $lines);
    }
}