<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\vfsStream;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\DatabaseDependantTestCase;

class ImportCsvToDatabaseCommandTest extends DatabaseDependantTestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $file_system;

    /**
     * @var CommandTester
     */
    private $tester;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @test */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('import-csv-to-database-command');
        $this->tester = new CommandTester($command);
    }

    /** @test */
    public function testExecuteWithRightMimeType()
    { 
        $this->tester->execute([
            'newFilePath' => './temp/stock.csv'
        ]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(file_exists('./temp/stock.csv'));
        $this->assertTrue(is_readable('./temp/stock.csv'));

        $this->assertStringContainsString('Items processed:', $this->tester->getDisplay());
        $this->assertStringContainsString('Items skipped (not to be imported):', $this->tester->getDisplay());
        $this->assertStringContainsString('Items successfully imported:', $this->tester->getDisplay());
    }

     /** @test */
     public function testExecuteWithTestVar()
     { 
        $this->tester->execute([
            'newFilePath' => './temp/stock.csv',
            'executionMode' => 'test'
        ]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(file_exists('./temp/stock.csv'));
        $this->assertTrue(is_readable('./temp/stock.csv'));
     }
 
    /** @test */
    public function testNotCsvFileGivesNoException()
    {
        $directory = [
          'test' => [
            'input.json' =>  '{"VALID_KEY":123}'
          ]
        ];

        $this->file_system = vfsStream::setup('root', 444, $directory);
        $this->tester->execute([
            'newFilePath' => $this->file_system->url().'/test/input.json'
        ]);

        $this->assertTrue(file_exists($this->file_system->url().'/test/input.json'));
        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('File is not CSV. Please use CSV files only.', $output);
    }

     /** @test */
     public function testEmptyCsvFileGivesNoExeption()
     {
        $this->tester->execute([
            'newFilePath' => './temp/empty.csv'
        ]);

        $this->assertTrue(file_exists('./temp/empty.csv'));
        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('File is not CSV. Please use CSV files only.', $output);

        $this->tester->assertCommandIsSuccessful();
     }

    /** @test */
     public function testFileThatNotExists()
     {
        $this->tester->execute([
            'newFilePath' => './temp/not_exists.csv'
        ]);

        $this->assertTrue(file_exists('./temp/empty.csv'));
        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('File not found. Please check path.', $output);

        $this->tester->assertCommandIsSuccessful();
    }
}