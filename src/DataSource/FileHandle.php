<?php

/**
 * Rych Bencode Component
 *
 * @package Rych\Bencode
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2014, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\Bencode\DataSource;

use Rych\Bencode\DataSource;
use Rych\Bencode\Exception\RuntimeException;

class FileHandle implements DataSource
{
    protected $handle;

    protected $length;

    public function __construct($handle)
    {
        if (!is_resource($handle)) {
            throw new RuntimeException('Expected resource, got '. gettype($string));
        }

        $this->handle = $handle;
    }

    public function getLength()
    {
        if (is_null($this->length)) {
            $stat = fstat($this->handle);
            $this->length = $stat['size'];
        }

        return $this->length;
    }

    public function read($offset, $length = 1)
    {
        $length = intval($length);

        if ($offset < 0 || $offset > $this->getLength() - 1) {
            throw new RuntimeException('Invalid offset');
        }

        if ($length < 1) {
            throw new RuntimeException('Invalid length');
        }

        fseek($this->handle, $offset);

        if ($length === 1) {
            return fgetc($this->handle);
        } else {
            return fread($this->handle, $length);
        }
    }
}