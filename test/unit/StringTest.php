<?php
/**
 * Rych Bencode
 *
 * Bencode serializer for PHP 5.3+.
 *
 * @package   Rych\Bencode
 * @copyright Copyright (c) 2014, Ryan Chouinard
 * @author    Ryan Chouinard <rchouinard@gmail.com>
 * @license   MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\Bencode;

use PHPUnit_Framework_TestCase as TestCase;
use Rych\Bencode\DataSource\String;

/**
 * Bencode string data source test
 */
class StringTest extends TestCase
{
    /**
     * Test that groups of characters can be read from a string
     *
     * @test
     */
    public function testGetChar()
    {
    	$raw = 'hello world';
        $string = new String($raw);

        $this->assertEquals(
        	'hello',
        	$string->read(0, 5)
    	);

    	$this->assertEquals(
        	'world',
        	$string->read(6, 5)
    	);

        $this->assertEquals(
            'ello wo',
            $string->read(1, 7)
        );

        for ($i = 0, $c = strlen($raw); $i < $c; $i++) {
        	$this->assertEquals(
        		$raw[$i],
        		$string->read($i)
        	);
        }
    }

    /**
     * Test that a string's length is reported correctly
     *
     * @test
     */
    public function testGetLength()
    {
        $raw = 'hello world';
        $string = new String($raw);

        $this->assertEquals(11, $string->getLength());
    }
}