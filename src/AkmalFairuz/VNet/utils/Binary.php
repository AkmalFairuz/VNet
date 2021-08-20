<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\utils;

use InvalidArgumentException;

/*
 * Source: https://github.com/pmmp/BinaryUtils
 */

class Binary{

    public static function signByte(int $value) : int{
        return $value << 56 >> 56;
    }

    public static function unsignByte(int $value) : int{
        return $value & 0xff;
    }

    public static function signShort(int $value) : int{
        return $value << 48 >> 48;
    }

    public static function unsignShort(int $value) : int{
        return $value & 0xffff;
    }

    public static function signInt(int $value) : int{
        return $value << 32 >> 32;
    }

    public static function unsignInt(int $value) : int{
        return $value & 0xffffffff;
    }

    public static function flipShortEndianness(int $value) : int{
        return self::readLShort(self::writeShort($value));
    }

    public static function flipIntEndianness(int $value) : int{
        return self::readLInt(self::writeInt($value));
    }

    public static function flipLongEndianness(int $value) : int{
        return self::readLLong(self::writeLong($value));
    }

    /**
     * @return mixed[]
     */
    private static function safeUnpack(string $formatCode, string $bytes) : array{
        //unpack SUCKS SO BADLY. We really need an extension to replace this garbage :(
        $result = unpack($formatCode, $bytes);
        if($result === false){
            //assume the formatting code is valid, since we provided it
            throw new BinaryDataException("Invalid input data (not enough?)");
        }
        return $result;
    }

    /**
     * Reads a byte boolean
     */
    public static function readBool(string $b) : bool{
        return $b !== "\x00";
    }

    /**
     * Writes a byte boolean
     */
    public static function writeBool(bool $b) : string{
        return $b ? "\x01" : "\x00";
    }

    /**
     * Reads an unsigned byte (0 - 255)
     */
    public static function readByte(string $c) : int{
        return ord($c[0]);
    }

    /**
     * Reads a signed byte (-128 - 127)
     */
    public static function readSignedByte(string $c) : int{
        return self::signByte(ord($c[0]));
    }

    /**
     * Writes an unsigned/signed byte
     */
    public static function writeByte(int $c) : string{
        return chr($c);
    }

    /**
     * Reads a 16-bit unsigned big-endian number
     */
    public static function readShort(string $str) : int{
        return self::safeUnpack("n", $str)[1];
    }

    /**
     * Reads a 16-bit signed big-endian number
     */
    public static function readSignedShort(string $str) : int{
        return self::signShort(self::safeUnpack("n", $str)[1]);
    }

    /**
     * Writes a 16-bit signed/unsigned big-endian number
     */
    public static function writeShort(int $value) : string{
        return pack("n", $value);
    }

    /**
     * Reads a 16-bit unsigned little-endian number
     */
    public static function readLShort(string $str) : int{
        return self::safeUnpack("v", $str)[1];
    }

    /**
     * Reads a 16-bit signed little-endian number
     */
    public static function readSignedLShort(string $str) : int{
        return self::signShort(self::safeUnpack("v", $str)[1]);
    }

    /**
     * Writes a 16-bit signed/unsigned little-endian number
     */
    public static function writeLShort(int $value) : string{
        return pack("v", $value);
    }

    /**
     * Reads a 3-byte big-endian number
     */
    public static function readTriad(string $str) : int{
        return self::safeUnpack("N", "\x00" . $str)[1];
    }

    /**
     * Writes a 3-byte big-endian number
     */
    public static function writeTriad(int $value) : string{
        return substr(pack("N", $value), 1);
    }

    /**
     * Reads a 3-byte little-endian number
     */
    public static function readLTriad(string $str) : int{
        return self::safeUnpack("V", $str . "\x00")[1];
    }

    /**
     * Writes a 3-byte little-endian number
     */
    public static function writeLTriad(int $value) : string{
        return substr(pack("V", $value), 0, -1);
    }

    /**
     * Reads a 4-byte signed integer
     */
    public static function readInt(string $str) : int{
        return self::signInt(self::safeUnpack("N", $str)[1]);
    }

    /**
     * Writes a 4-byte integer
     */
    public static function writeInt(int $value) : string{
        return pack("N", $value);
    }

    /**
     * Reads a 4-byte signed little-endian integer
     */
    public static function readLInt(string $str) : int{
        return self::signInt(self::safeUnpack("V", $str)[1]);
    }

    /**
     * Writes a 4-byte signed little-endian integer
     */
    public static function writeLInt(int $value) : string{
        return pack("V", $value);
    }

    /**
     * Reads a 4-byte floating-point number
     */
    public static function readFloat(string $str) : float{
        return self::safeUnpack("G", $str)[1];
    }

    /**
     * Reads a 4-byte floating-point number, rounded to the specified number of decimal places.
     */
    public static function readRoundedFloat(string $str, int $accuracy) : float{
        return round(self::readFloat($str), $accuracy);
    }

    /**
     * Writes a 4-byte floating-point number.
     */
    public static function writeFloat(float $value) : string{
        return pack("G", $value);
    }

    /**
     * Reads a 4-byte little-endian floating-point number.
     */
    public static function readLFloat(string $str) : float{
        return self::safeUnpack("g", $str)[1];
    }

    /**
     * Reads a 4-byte little-endian floating-point number rounded to the specified number of decimal places.
     */
    public static function readRoundedLFloat(string $str, int $accuracy) : float{
        return round(self::readLFloat($str), $accuracy);
    }

    /**
     * Writes a 4-byte little-endian floating-point number.
     */
    public static function writeLFloat(float $value) : string{
        return pack("g", $value);
    }

    /**
     * Returns a printable floating-point number.
     */
    public static function printFloat(float $value) : string{
        return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
    }

    /**
     * Reads an 8-byte floating-point number.
     */
    public static function readDouble(string $str) : float{
        return self::safeUnpack("E", $str)[1];
    }

    /**
     * Writes an 8-byte floating-point number.
     */
    public static function writeDouble(float $value) : string{
        return pack("E", $value);
    }

    /**
     * Reads an 8-byte little-endian floating-point number.
     */
    public static function readLDouble(string $str) : float{
        return self::safeUnpack("e", $str)[1];
    }

    /**
     * Writes an 8-byte floating-point little-endian number.
     */
    public static function writeLDouble(float $value) : string{
        return pack("e", $value);
    }

    /**
     * Reads an 8-byte integer.
     */
    public static function readLong(string $str) : int{
        return self::safeUnpack("J", $str)[1];
    }

    /**
     * Writes an 8-byte integer.
     */
    public static function writeLong(int $value) : string{
        return pack("J", $value);
    }

    /**
     * Reads an 8-byte little-endian integer.
     */
    public static function readLLong(string $str) : int{
        return self::safeUnpack("P", $str)[1];
    }

    /**
     * Writes an 8-byte little-endian integer.
     */
    public static function writeLLong(int $value) : string{
        return pack("P", $value);
    }

    /**
     * Reads a 32-bit zigzag-encoded variable-length integer.
     *
     * @param int    $offset reference parameter
     */
    public static function readVarInt(string $buffer, int &$offset) : int{
        $raw = self::readUnsignedVarInt($buffer, $offset);
        $temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
        return $temp ^ ($raw & (1 << 63));
    }

    /**
     * Reads a 32-bit variable-length unsigned integer.
     *
     * @param int    $offset reference parameter
     *
     * @throws BinaryDataException if the var-int did not end after 5 bytes or there were not enough bytes
     */
    public static function readUnsignedVarInt(string $buffer, int &$offset) : int{
        $value = 0;
        for($i = 0; $i <= 28; $i += 7){
            if(!isset($buffer[$offset])){
                throw new BinaryDataException("No bytes left in buffer");
            }
            $b = ord($buffer[$offset++]);
            $value |= (($b & 0x7f) << $i);

            if(($b & 0x80) === 0){
                return $value;
            }
        }

        throw new BinaryDataException("VarInt did not terminate after 5 bytes!");
    }

    /**
     * Writes a 32-bit integer as a zigzag-encoded variable-length integer.
     */
    public static function writeVarInt(int $v) : string{
        $v = ($v << 32 >> 32);
        return self::writeUnsignedVarInt(($v << 1) ^ ($v >> 31));
    }

    /**
     * Writes a 32-bit unsigned integer as a variable-length integer.
     *
     * @return string up to 5 bytes
     */
    public static function writeUnsignedVarInt(int $value) : string{
        $buf = "";
        $value &= 0xffffffff;
        for($i = 0; $i < 5; ++$i){
            if(($value >> 7) !== 0){
                $buf .= chr($value | 0x80);
            }else{
                $buf .= chr($value & 0x7f);
                return $buf;
            }

            $value = (($value >> 7) & (PHP_INT_MAX >> 6)); //PHP really needs a logical right-shift operator
        }

        throw new InvalidArgumentException("Value too large to be encoded as a VarInt");
    }

    /**
     * Reads a 64-bit zigzag-encoded variable-length integer.
     *
     * @param int    $offset reference parameter
     */
    public static function readVarLong(string $buffer, int &$offset) : int{
        $raw = self::readUnsignedVarLong($buffer, $offset);
        $temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
        return $temp ^ ($raw & (1 << 63));
    }

    /**
     * Reads a 64-bit unsigned variable-length integer.
     *
     * @param int    $offset reference parameter
     *
     * @throws BinaryDataException if the var-int did not end after 10 bytes or there were not enough bytes
     */
    public static function readUnsignedVarLong(string $buffer, int &$offset) : int{
        $value = 0;
        for($i = 0; $i <= 63; $i += 7){
            if(!isset($buffer[$offset])){
                throw new BinaryDataException("No bytes left in buffer");
            }
            $b = ord($buffer[$offset++]);
            $value |= (($b & 0x7f) << $i);

            if(($b & 0x80) === 0){
                return $value;
            }
        }

        throw new BinaryDataException("VarLong did not terminate after 10 bytes!");
    }

    /**
     * Writes a 64-bit integer as a zigzag-encoded variable-length long.
     */
    public static function writeVarLong(int $v) : string{
        return self::writeUnsignedVarLong(($v << 1) ^ ($v >> 63));
    }

    /**
     * Writes a 64-bit unsigned integer as a variable-length long.
     */
    public static function writeUnsignedVarLong(int $value) : string{
        $buf = "";
        for($i = 0; $i < 10; ++$i){
            if(($value >> 7) !== 0){
                $buf .= chr($value | 0x80); //Let chr() take the last byte of this, it's faster than adding another & 0x7f.
            }else{
                $buf .= chr($value & 0x7f);
                return $buf;
            }

            $value = (($value >> 7) & (PHP_INT_MAX >> 6)); //PHP really needs a logical right-shift operator
        }
        throw new InvalidArgumentException("Value too large to be encoded as a VarLong");
    }
}