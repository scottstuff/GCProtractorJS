<?php

namespace GotChosen\Util;

class Enums
{
    /**
     * @param $value
     * @param array $allowed
     * @throws \InvalidArgumentException
     */
    public static function assert($value, array $allowed)
    {
        if ( !in_array($value, array_keys($allowed)) ) {
            throw new \InvalidArgumentException("Value '$value' is not allowed. Please use one of the following:\n"
                . print_r($allowed, true));
        }
    }
}