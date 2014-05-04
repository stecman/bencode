<?php

namespace Rych\Bencode;

use Rych\Bencode\DataSource\FileHandle;

class FileHandleTest extends \PHPUnit_Framework_TestCase
{
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