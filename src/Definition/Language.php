<?php

namespace GoPay\Definition;

class Language
{
    const CZECH = 'CS';
    const ENGLISH = 'EN';
    const SLOVAK = 'SK';
    const GERMAN = 'DE';
    const RUSSIAN = 'RU';

    const LOCALE_CZECH = 'cs-CZ';
    const LOCALE_ENGLISH = 'en-US';

    public static function getAcceptedLocale($language)
    {
        static $czechLike = [Language::CZECH, Language::SLOVAK];
        return in_array($language, $czechLike) ? self::LOCALE_CZECH : self::LOCALE_ENGLISH;
    }
}
