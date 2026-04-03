<?php
namespace App\Helpers;

class Text
{
    /**
     * Normaliza texto para Title Case em PT mantendo preposicoes/artigos em minusculas
     * (exceto na primeira palavra).
     */
    public static function toPortugueseTitleCase(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
        if ($value === '') {
            return '';
        }

        $lowerWords = [
            'a', 'ao', 'aos', 'as', 'com', 'da', 'das', 'de', 'do', 'dos', 'e', 'em',
            'na', 'nas', 'no', 'nos', 'o', 'os', 'ou', 'para', 'por', 'sem', 'sob',
            'um', 'uma', 'uns', 'umas'
        ];
        $lowerMap = array_fill_keys($lowerWords, true);

        $parts = preg_split('/([\-\/])/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($parts)) {
            return $value;
        }

        $result = [];
        $wordIndex = 0;

        foreach ($parts as $part) {
            if ($part === '-' || $part === '/') {
                $result[] = $part;
                continue;
            }

            $tokens = preg_split('/\s+/u', $part);
            if (!is_array($tokens)) {
                continue;
            }

            foreach ($tokens as $token) {
                if ($token === '') {
                    continue;
                }

                $tokenLower = mb_strtolower($token, 'UTF-8');
                if ($wordIndex > 0 && isset($lowerMap[$tokenLower])) {
                    $result[] = $tokenLower;
                } else {
                    $first = mb_substr($tokenLower, 0, 1, 'UTF-8');
                    $rest = mb_substr($tokenLower, 1, null, 'UTF-8');
                    $result[] = mb_strtoupper($first, 'UTF-8') . $rest;
                }

                $wordIndex++;
            }
        }

        return implode(' ', $result);
    }
}
