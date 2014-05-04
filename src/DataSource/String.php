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

class String implements DataSource
{
    protected $string;

    protected $length;

    public function __construct($string)
    {
        if (!is_string($string)) {
            throw new RuntimeException('Expected string, got '. gettype($string));
        }

        $this->string = $string;
    }

    public function getLength()
    {
        if (is_null($this->length)) {
            $this->length = strlen($this->string);
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

        if ($length === 1) {
            return $this->string[$offset];
        } else {
            return substr($this->string, $offset, $length);
        }
    }
}