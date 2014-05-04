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

use Rych\Bencode\Exception\RuntimeException;

/**
 * Bencode decoder class
 *
 * Decodes bencode encoded strings.
 */
class Decoder
{

    /**
     * The encoded source string
     *
     * @var string
     */
    protected $source;

    /**
     * The length of the encoded source string
     *
     * @var integer
     */
    protected $decodeType;

    /**
     * The return type for the decoded value
     *
     * @var Bencode::TYPE_ARRAY|Bencode::TYPE_OBJECT
     */
    protected $sourceLength;

    /**
     * The current offset of the parser.
     *
     * @var integer
     */
    protected $offset = 0;

    /**
     * Decoder constructor
     *
     * @param  string  $source The bencode encoded source.
     * @param  string  $decodeType Flag used to indicate whether the decoded
     *   value should be returned as an object or an array.
     * @return void
     */
    protected function __construct(DataSource $source, $decodeType)
    {
        $this->source = $source;
        $this->sourceLength = $source->getLength($source);
        if ($decodeType != Bencode::TYPE_ARRAY && $decodeType != Bencode::TYPE_OBJECT) {
            $decodeType = Bencode::TYPE_ARRAY;
        }
        $this->decodeType = $decodeType;
    }

    /**
     * Decode a bencode encoded string
     *
     * @param  string $source The string to decode.
     * @param  string $decodeType Flag used to indicate whether the decoded
     *   value should be returned as an object or an array.
     * @return mixed   Returns the appropriate data type for the decoded data.
     * @throws RuntimeException
     */
    public static function decode(DataSource $source, $decodeType = Bencode::TYPE_ARRAY)
    {
        $decoder = new self($source, $decodeType);
        $decoded = $decoder->doDecode();

        if ($decoder->offset != $decoder->sourceLength) {
            throw new RuntimeException("Found multiple entities outside list or dict definitions");
        }

        return $decoded;
    }

    /**
     * Iterate over encoded entities in the source string and decode them
     *
     * @return mixed   Returns the decoded value.
     * @throws RuntimeException
     */
    protected function doDecode()
    {
        switch ($this->getChar()) {

            case "i":
                ++$this->offset;
                return $this->decodeInteger();

            case "l":
                ++$this->offset;
                return $this->decodeList();

            case "d":
                ++$this->offset;
                return $this->decodeDict();

            default:
                if (ctype_digit($this->getChar())) {
                    return $this->decodeString();
                }

        }

        throw new RuntimeException("Unknown entity found at offset $this->offset");
    }

    /**
     * Decode a bencode encoded integer
     *
     * @return integer Returns the decoded integer.
     * @throws RuntimeException
     */
    protected function decodeInteger()
    {
        $currentOffset = $this->offset;
        $value = '';

        if ('-' == $this->getChar($currentOffset)) {
            $value = '-';
            ++$currentOffset;
        }

        $char = $this->getChar($currentOffset);

        while ($char !== 'e') {
            if (!ctype_digit($char)) {
                throw new RuntimeException('Non-numeric character found in integer entity at offset ' . $this->offset);
            }
            $value .= $char;
            $char = $this->getChar(++$currentOffset);
        }

        if (trim($value,'-') === '') {
            throw new RuntimeException('Integer was empty at offset ' . $this->offset);
        }

        // One last check to make sure zero-padded integers don't slip by, as
        // they're not allowed per bencode specification.
        $absoluteValue = (string) abs($value);
        if (1 < strlen($absoluteValue) && "0" == $value[0]) {
            throw new RuntimeException("Illegal zero-padding found in integer entity at offset $this->offset");
        }

        $this->offset += $currentOffset;

        // The +0 auto-casts the chunk to either an integer or a float(in cases
        // where an integer would overrun the max limits of integer types)
        return $value + 0;
    }

    /**
     * Decode a bencode encoded string
     *
     * @return string  Returns the decoded string.
     * @throws RuntimeException
     */
    protected function decodeString()
    {
        if ("0" === $this->getChar() && ":" != $this->getChar($this->offset + 1)) {
            throw new RuntimeException("Illegal zero-padding in string entity length declaration at offset $this->offset");
        }

        $char = $this->getChar();
        $length = '';

        while ($char !== ':') {
            if (!ctype_digit($char)) {
                throw new RuntimeException('Unterminated string entity at offset ' . $this->offset);
            }
            $length .= $char;
            $char = $this->getChar(++$this->offset);
        }

        $length = (int) $length;

        if (($length + $this->offset + 1) > $this->sourceLength) {
            throw new RuntimeException('Unexpected end of string entity at offset ' . $this->offset);
        }

        $value = $this->getChar(++$this->offset, $length);
        $this->offset += $length;

        return $value;
    }

    /**
     * Decode a bencode encoded list
     *
     * @return array   Returns the decoded array.
     * @throws RuntimeException
     */
    protected function decodeList()
    {
        $list = array();
        $terminated = false;
        $listOffset = $this->offset;

        while (false !== $this->getChar()) {
            if ("e" == $this->getChar()) {
                $terminated = true;
                break;
            }

            $list[] = $this->doDecode();
        }

        if (!$terminated && false === $this->getChar()) {
            throw new RuntimeException("Unterminated list definition at offset $listOffset");
        }

        $this->offset++;

        return $list;
    }

    /**
     * Decode a bencode encoded dictionary
     *
     * @return array   Returns the decoded array.
     * @throws RuntimeException
     */
    protected function decodeDict()
    {
        $dict = array();
        $terminated = false;
        $dictOffset = $this->offset;

        while (false !== $this->getChar()) {
            if ("e" == $this->getChar()) {
                $terminated = true;
                break;
            }

            $keyOffset = $this->offset;
            if (!ctype_digit($this->getChar())) {
                throw new RuntimeException("Invalid dictionary key at offset $keyOffset");
            }

            $key = $this->decodeString();
            if (isset ($dict[$key])) {
                throw new RuntimeException("Duplicate dictionary key at offset $keyOffset");
            }

            $dict[$key] = $this->doDecode();
        }

        if (!$terminated && false === $this->getChar()) {
            throw new RuntimeException("Unterminated dictionary definition at offset $dictOffset");
        }

        $this->offset++;

        return $dict;
    }

    /**
     * Fetch the character at the specified source offset
     *
     * If offset is not provided, the current offset is used.
     *
     * @param  integer $offset The offset to retrieve from the source string.
     * @return string|false Returns the character found at the specified
     *   offset. If the specified offset is out of range, FALSE is returned.
     */
    protected function getChar($offset = null, $length = 1)
    {
        if (null === $offset) {
            $offset = $this->offset;
        }

        if ($this->offset >= $this->sourceLength) {
            return false;
        }

        return $this->source->read($offset, $length);
    }

    protected function getLength($source)
    {
        return strlen($source);
    }

}
