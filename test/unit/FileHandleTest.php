<?php

namespace Rych\Bencode;

use PHPUnit_Framework_TestCase as TestCase;
use Rych\Bencode\DataSource\FileHandle;

/**
 * Bencode file data source test
 */
class FileHandleTest extends TestCase
{
    /**
     * Test that groups of characters can be read from a file
     *
     * @test
     */
    public function testGetChar()
    {
        $raw = 'hello world';
        $handle = fopen('php://memory', 'w+');
        fwrite($handle, $raw);

        $file = new FileHandle($handle);

        $this->assertEquals(
            'hello',
            $file->read(0, 5)
        );

        $this->assertEquals(
            'world',
            $file->read(6, 5)
        );

        for ($i = 0, $c = strlen($raw); $i < $c; $i++) {
            $this->assertEquals(
                $raw[$i],
                $file->read($i)
            );
        }

        fclose($handle);
    }

    /**
     * Test that a file's length is reported correctly
     *
     * @test
     */
    public function testGetLength()
    {
        $raw = 'hello world';
        $handle = fopen('php://memory', 'w+');
        fwrite($handle, $raw);

        $file = new FileHandle($handle);

        $this->assertEquals(11, $file->getLength());

        fclose($handle);
    }
}