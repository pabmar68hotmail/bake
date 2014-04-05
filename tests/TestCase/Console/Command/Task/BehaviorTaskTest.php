<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Test\TestCase\Console\Command\Task;

use Cake\Console\Command\Task\TemplateTask;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;

/**
 * BehaviorTaskTest class
 *
 */
class BehaviorTaskTest extends TestCase {

/**
 * setup method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$out = $this->getMock('Cake\Console\ConsoleOutput', [], [], '', false);
		$in = $this->getMock('Cake\Console\ConsoleInput', [], [], '', false);

		$this->Task = $this->getMock('Cake\Console\Command\Task\BehaviorTask',
			['in', 'err', 'createFile', '_stop', 'clear'],
			[$out, $out, $in]
		);
		$this->Task->Test = $this->getMock('Cake\Console\Command\Task\TestTask',
			[],
			[$out, $out, $in]
		);
		$this->Task->Template = new TemplateTask($out, $out, $in);
		$this->Task->Template->initialize();
	}

/**
 * Test the excute method.
 *
 * @return void
 */
	public function testExecute() {
		$this->Task->args = ['Example'];
		$this->Task->expects($this->once())
			->method('createFile')
			->with(
				APP . 'Model/Behavior/ExampleBehavior.php',
				$this->stringContains('class ExampleBehavior extends Behavior')
			);
		$this->Task->Test->expects($this->once())
			->method('bake')
			->with('behavior', 'Example');

		$this->Task->execute();
	}

/**
 * Test generating code.
 *
 * @return void
 */
	public function testBake() {
		Configure::write('App.namespace', 'TestApp');

		$this->Task->expects($this->once())
			->method('createFile')
			->with(
				APP . 'Model/Behavior/ExampleBehavior.php',
				$this->stringContains('class ExampleBehavior extends Behavior')
			);

		$result = $this->Task->bake('Example');
		$this->assertContains('namespace TestApp\Model\Behavior;', $result);
		$this->assertContains('use Cake\ORM\Behavior;', $result);
		$this->assertContains('class ExampleBehavior extends Behavior {', $result);
	}

/**
 * Test bakeTest
 *
 * @return void
 */
	public function testBakeTest() {
		$this->Task->plugin = 'TestPlugin';
		$this->Task->Test->expects($this->once())
			->method('bake')
			->with('behavior', 'Example');

		$this->Task->bakeTest('Example');
		$this->assertEquals($this->Task->plugin, $this->Task->Test->plugin);
	}

/**
 * Test the no-test option.
 *
 * @return void
 */
	public function testBakeTestNoTest() {
		$this->Task->params['no-test'] = true;
		$this->Task->Test->expects($this->never())
			->method('bake');

		$result = $this->Task->bakeTest('Example');
	}

/**
 * Test baking within a plugin.
 *
 * @return void
 */
	public function testBakePlugin() {
		Plugin::load('TestPlugin');

		$path = Plugin::path('TestPlugin');

		$this->Task->plugin = 'TestPlugin';
		$this->Task->expects($this->once())
			->method('createFile')
			->with(
				$path . 'Model/Behavior/ExampleBehavior.php',
				$this->stringContains('class ExampleBehavior extends Behavior')
			);

		$result = $this->Task->bake('Example');
		$this->assertContains('namespace TestPlugin\Model\Behavior;', $result);
		$this->assertContains('use Cake\ORM\Behavior;', $result);
		$this->assertContains('class ExampleBehavior extends Behavior {', $result);
	}

}
