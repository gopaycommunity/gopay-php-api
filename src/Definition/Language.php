<?php

namespace GoPay\Definition;

class Language
{
    const CZECH = 'CS';
    const ENGLISH = 'EN';
    const SLOVAK = 'SK';
    const GERMAN = 'DE';
    const RUSSIAN = 'RU';

    public static function getAcceptedLocale($language)
    {
        static $czechLike = [Language::CZECH, Language::SLOVAK];
        return in_array($language, $czechLike) ? 'cs-CZ' : 'en-US';
    }
}