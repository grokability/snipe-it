<?php

namespace App\Services;

use App\Helpers\Helper;
use Illuminate\Translation\Translator;

/***************************************************************
 * This is just a very, very light modification to the default Laravel Translator.
 * The only difference it has is that it modifies the $locale
 * value when the pluralizations are done so we can switch over from en-US to en_US (for example).
 *
 * This means our translation directories can stay where they are (en-US), but the
 * pluralization code can get executed against a locale of en_US
 * (Which is required by Symfony, for some inexplicable reason)
 *
 * This method is called by the trans_choice() helper, which we *do* use a lot.
 ***************************************************************/
class SnipeTranslator extends Translator {

    static $legacy_translation_namespaces = [
        "backup::" //Spatie backup uses 'legacy' locale names
    ];

    //This is copied-and-pasted (almost) verbatim from Illuminate\Translation\Translator
    public function choice($key, $number, array $replace = [], $locale = null)
    {
        $line = $this->get(
            $key, [], $locale = $this->localeForChoice($key, $locale)
        );

        // If the given "number" is actually an array or countable we will simply count the
        // number of elements in an instance. This allows developers to pass an array of
        // items without having to count it on their end first which gives bad syntax.
        if (is_countable($number)) {
            $number = count($number);
        }

        if (!isset($replace['count'])) {
            $replace['count'] = $number;
        }

        $underscored_locale = str_replace("-","_",$locale); // OUR CHANGE.
        return $this->makeReplacements( // BELOW - that $underscored_locale is the *ONLY* modified part
            $this->getSelector()->choose($line, $number, $underscored_locale), $replace
        );
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $modified_locale = $locale;
        $changed_fallback = false;
        $previous_fallback = $this->fallback;
        // 'legacy' translation directories tend to be two-char ('en'), not 5-char ('en-US').
        // Here we try our best to handle that.
        foreach (self::$legacy_translation_namespaces as $namespace) {
            if (preg_match("/^$namespace/", $key)) {
                $modified_locale = Helper::mapBackToLegacyLocale($locale);
                $changed_fallback = true;
                $this->fallback = 'en'; //TODO - should this be 'env-able'? Or do we just put our foot down and say 'en'?
                break;
            }
        }

        $result = parent::get($key, $replace, $modified_locale, $fallback);
        if ($changed_fallback) {
            $this->fallback = $previous_fallback;
        }
        return $result;
    }


}