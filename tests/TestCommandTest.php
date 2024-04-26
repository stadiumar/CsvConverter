<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\vfsStream;
use App\Command\TestCommand;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\DatabasePrimer;

class TestCommandTest extends KernelTestCase
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
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    /** @test */
    public function testExecuteWithRightMimeType()
    { 
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('test-command');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'newFilePath' => './temp/stock.csv'
        ]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertTrue(file_exists('./temp/stock.csv'));
        $this->assertTrue(is_readable('./temp/stock.csv'));

        $this->assertStringContainsString('Items processed:', $commandTester->getDisplay());
        $this->assertStringContainsString('Items skipped (not to be imported):', $commandTester->getDisplay());
        $this->assertStringContainsString('Items successfully imported:', $commandTester->getDisplay());
    }

     /** @test */
     public function testExecuteWithTestVar()
     { 
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('test-command');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'newFilePath' => './temp/stock.csv',
            'executionMode' => 'test'
        ]);

        $commandTester->assertCommandIsSuccessful();
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
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('test-command');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'newFilePath' => $this->file_system->url().'/test/input.json'
        ]);

        $this->assertTrue(file_exists($this->file_system->url().'/test/input.json'));
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('File is not CSV. Please use CSV files only.', $output);
    }

     /** @test */
     public function testEmptyCsvFileGivesNoExeption()
     {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('test-command');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'newFilePath' => './temp/empty.csv'
        ]);

        $this->assertTrue(file_exists('./temp/empty.csv'));
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('File is not CSV. Please use CSV files only.', $output);

        $commandTester->assertCommandIsSuccessful();
     }

     public function testFileThatNotExists()
     {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('test-command');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'newFilePath' => './temp/not_exists.csv'
        ]);

        $this->assertTrue(file_exists('./temp/empty.csv'));
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('File not found. Please check path.', $output);

        $commandTester->assertCommandIsSuccessful();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}