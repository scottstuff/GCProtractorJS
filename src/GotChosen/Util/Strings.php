<?php

namespace GotChosen\Util;

class Strings
{
    public static function base64EncodeUrl($text)
    {
        $enc = base64_encode($text);
        $enc = strtr($enc, '+/=', '._-');
        return $enc;
    }

    public static function base64DecodeUrl($enc)
    {
        $enc = strtr($enc, '._-', '+/=');
        $text = base64_decode($enc);
        return $text;
    }
    
    /**
     * Slugify a string - There's no reason this shouldn't work since we're
     * using PHP 5.4+ everywhere?
     * 
     * @param string $string
     * @return string
     */
    public static function slugify($string)
    {
        $string = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $string);
        $string = preg_replace('/[-\s]+/', '-', $string);
        return trim($string, '-');
    }
}