<?php
/**
 * BasicSeedShellTest file
 */
namespace Cake\Test\TestCase\Shell;

use BasicSeed\Shell\BasicSeedShell;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\TestSuite\TestCase;

/**
 * Class BasicSeedShellTest
 *
 */
class BasicSeedShellTest extends TestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->out = new ConsoleOutput();
		$io = new ConsoleIo($this->out);
		$this->Shell = $this->getMock(
			'BasicSeedShell',
			['in', 'err', '_stop', 'clear'],
			[$io]
		);
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Shell);
		parent::tearDown();
	}

	/**
	 * test that main finds core shells.
	 *
	 * @return void
	 */
	public function testMain() {
		$this->Repl->expects($this->once())
			->method('run');
		$this->Shell->main();
	}

	/**
	 * test that main finds core shells.
	 *
	 * @return void
	 */
	public function testGetOptionParser() {
		$result = $this->Shell->getOptionParser();
		$this->assertInstanceOf(
			'Cake\Console\ConsoleOptionParser',
			$result,
			'Cursory sanity check. getOptionParser() should return an option parse instance.'
		);
	}
}
