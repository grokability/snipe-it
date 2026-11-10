<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Prompt;

abstract class SnipePrompt extends Prompt
{
    /**
     * Returns a trailing instruction telling the model which language to respond in,
     * derived from the authenticated user's locale setting. Returns an empty string
     * for English locales so the prompt text is unchanged for the majority of users.
     */
    protected function localeInstruction(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();

        if (str_starts_with($locale, 'en')) {
            return '';
        }

        return "\n\nPlease respond in the language that corresponds to locale: {$locale}.";
    }
}
