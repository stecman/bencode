<?php
/**
 * Rych Bencode Component
 *
 * @package Rych\Bencode
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2014, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\Bencode;

interface DataSource
{
    /**
     * Get the length of the data source
     * 
     * @return integer
     */
    public function getLength();

    /**
     * Read bytes from the data source
     * 
     * @param  integer $offset
     * @param  integer $length
     * @return string
     */
    public function read($offset, $length = null);
}