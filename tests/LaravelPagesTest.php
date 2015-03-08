<?php

use Illuminate\Foundation\Testing\TestCase;
use JeroenG\LaravelPages\LaravelPages;

/**
 * This is for testing the package
 *
 * @package LaravelPages
 * @subpackage Tests
 * @author 	JeroenG
 * 
 **/
class LaravelPagesTest extends TestCase
{

	/**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $app->register('JeroenG\LaravelPages\LaravelPagesServiceProvider');

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;      
    }

    /**
     * Setup DB before each test.
     *
     * @return void  
     */
    public function setUp()
    { 
        parent::setUp();

        $this->app['config']->set('database.default','sqlite'); 
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');

        $this->migrate();

        $this->pages = new LaravelPages();
    }

    /**
     * run package database migrations
     *
     * @return void
     */
    public function migrate()
    { 
        $classFinder = $this->app->make('Illuminate\Filesystem\ClassFinder');
        
        $path = realpath(__DIR__ . "/../src/migrations");
        $files = glob($path.'/*');

        foreach($files as $file)
        {
            require_once $file;
            $migrationClass = $classFinder->findClass($file);

            (new $migrationClass)->up();
        }
    }

	/**
     * Test adding a new page.
     *
     * @test
     */
	public function testAddPage()
	{
		$page_title = "Hello Europe";
		$page_content = "This is the content for another page";
		$output = $this->pages->addPage($page_title, $page_content);
		$this->assertTrue($output);
	}

	/**
     * Test if the page does exists.
     *
     * @test
     */
	public function testPageExists()
	{
        $this->dummy();
		$output = $this->pages->PageExists('hello-world');
		$this->assertTrue($output);
	}

	/**
     * Test if the page does NOT exists.
     *
     * @test
     */
	public function testPageNotExists()
	{
		$output = $this->pages->PageExists('hello-universe');
		$this->assertFalse($output);
	}

	/**
     * Test getting the page data.
     *
     * @test
     */
	public function testGetPage()
	{
        $this->dummy();
		$output = $this->pages->getPage('hello-world');
		$this->assertEquals(7, count($output));
		$this->assertContains('Dummy Content', $output);
	}

	/**
     * Test getting the page id.
     *
     * @test
     */
	public function testGetPageId()
	{
        $this->dummy();
		$output = $this->pages->getPageId('hello-world');
		$this->assertEquals(1, $output);
	}

	/**
     * Test getting the id of a soft-deleted page. Also tests restoring.
     *
     * @test
     */
	public function testGetDeletedPageId()
	{
        $this->dummy();
		$this->pages->deletePage(1);
		$output = $this->pages->getPageId('hello-world');
		$this->pages->restorePage(1);
		$this->assertEquals(1, $output);
	}

	/**
     * Test getting the id of a force-deleted page, because it shouldn't work.
     *
     * @test
     */
	public function testGetForceDeletedPageId()
	{
        $this->dummy();
		$this->setExpectedException('ErrorException');
		$this->pages->deletePage(1, true);
		$output = $this->pages->getPageId(1);
	}

    public function dummy()
    {
        $this->pages->addPage('Test', 'Dummy Content', 'hello-world');
    }
}