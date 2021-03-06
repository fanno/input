<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\Cli;
use Joomla\Test\TestHelper;

require_once __DIR__ . '/Stubs/FilterInputMock.php';

/**
 * Test class for JInput.
 *
 * @since  1.0
 */
class CliTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test the Joomla\Input\Cli::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Cli::get
	 * @since   1.0
	 */
	public function testGet()
	{
		$_SERVER['argv'] = array('/dev/null', '--foo=bar', '-ab', 'blah', '-g', 'flower sakura');
		$instance = new Cli(null, array('filter' => new FilterInputMock));

		$this->assertThat(
			$instance->get('foo'),
			$this->identicalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->get('a'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->get('b'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->args,
			$this->equalTo(array('blah')),
			'Line: ' . __LINE__ . '.'
		);

		// Default filter
		$this->assertEquals(
			'flower sakura',
			$instance->get('g'),
			'Default filter should be string. Line: ' . __LINE__
		);
	}

	/**
	 * Test the Joomla\Input\Cli::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Cli::get
	 * @since   1.0
	 */
	public function testParseLongArguments()
	{
		$_SERVER['argv'] = array('/dev/null', '--ab', 'cd', '--ef', '--gh=bam');
		$instance = new Cli(null, array('filter' => new FilterInputMock));

		$this->assertThat(
			$instance->get('ab'),
			$this->identicalTo('cd'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->get('ef'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->get('gh'),
			$this->identicalTo('bam'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->args,
			$this->equalTo(array()),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the Joomla\Input\Cli::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Cli::get
	 * @since   1.0
	 */
	public function testParseShortArguments()
	{
		$_SERVER['argv'] = array('/dev/null', '-ab', '-c', '-e', 'f', 'foobar', 'ghijk');
		$instance = new Cli(null, array('filter' => new FilterInputMock));

		$this->assertThat(
			$instance->get('a'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->get('b'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->get('c'),
			$this->identicalTo(true),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->get('e'),
			$this->identicalTo('f'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$instance->args,
			$this->equalTo(array('foobar', 'ghijk')),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the JInput::parseArguments method.
	 *
	 * @dataProvider provider_parseArguments
	 */
	public function testParseArguments($inputArgv, $expectedData, $expectedArgs)
	{
		$_SERVER['argv'] = $inputArgv;
		$instance = new Cli(null, array('filter' => new FilterInputMock));

		$this->assertThat(
			TestHelper::getValue($instance, 'data'),
			$this->identicalTo($expectedData)
		);

		$this->assertThat(
			$instance->args,
			$this->identicalTo($expectedArgs)
		);
	}

	/**
	 * Test inputs:
	 *
	 * php test.php --foo --bar=baz
	 * php test.php -abc
	 * php test.php arg1 arg2 arg3
	 * php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --also-funny=spam=eggs \
	 *     'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
	 * php test.php --key value -abc not-c-value
	 * php test.php --key1 value1 -a --key2 -b b-value --c
	 *
	 * Note that this pattern is not supported: -abc c-value
	 */
	public function provider_parseArguments()
	{
		return array(

			// php test.php --foo --bar=baz
			array(
				array('test.php', '--foo', '--bar=baz'),
				array(
					'foo' => true,
					'bar' => 'baz'
				),
				array()
			),

			// php test.php -abc
			array(
				array('test.php', '-abc'),
				array(
					'a' => true,
					'b' => true,
					'c' => true
				),
				array()
			),

			// php test.php arg1 arg2 arg3
			array(
				array('test.php', 'arg1', 'arg2', 'arg3'),
				array(),
				array(
					'arg1',
					'arg2',
					'arg3'
				)
			),

			// php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --also-funny=spam=eggs \
			//      'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
			array(
				array('test.php', 'plain-arg', '--foo', '--bar=baz', '--funny=spam=eggs', '--also-funny=spam=eggs',
					'plain arg 2', '-abc', '-k=value', 'plain arg 3', '--s=original', '--s=overwrite', '--s'),
				array(
					'foo' => true,
					'bar' => 'baz',
					'funny' => 'spam=eggs',
					'also-funny' => 'spam=eggs',
					'a' => true,
					'b' => true,
					'c' => true,
					'k' => 'value',
					's' => 'overwrite'
				),
				array(
					'plain-arg',
					'plain arg 2',
					'plain arg 3'
				)
			),

			// php test.php --key value -abc not-c-value
			array(
				array('test.php', '--key', 'value', '-abc', 'not-c-value'),
				array(
					'key' => 'value',
					'a' => true,
					'b' => true,
					'c' => true
				),
				array(
					'not-c-value'
				)
			),

			// php test.php --key1 value1 -a --key2 -b b-value --c
			array(
				array('test.php', '--key1', 'value1', '-a', '--key2', '-b', 'b-value', '--c'),
				array(
					'key1' => 'value1',
					'a' => true,
					'key2' => true,
					'b' => 'b-value',
					'c' => true
				),
				array()
			)
		);
	}

	/**
	 * Test the Joomla\Input\Cli::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Cli::get
	 * @since   1.0
	 */
	public function testGetFromServer()
	{
		$instance = new Cli(null, array('filter' => new FilterInputMock));

		// Check the object type.
		$this->assertInstanceOf(
			'Joomla\\Input\\Input',
			$instance->server,
			'Line: ' . __LINE__ . '.'
		);

		// Test the get method.
		$this->assertThat(
			$instance->server->get('PHP_SELF'),
			$this->identicalTo($_SERVER['PHP_SELF']),
			'Line: ' . __LINE__ . '.'
		);
	}
}
