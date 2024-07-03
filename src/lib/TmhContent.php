<?php

namespace lib;

class TmhContent
{
    public function minimizeAttributes(array $attributes): array
    {
        $keysToRemove = [
            'active',
            'comment',
            'country',
            'innerHtml',
            'meta_description',
            'meta_keywords',
            'page_title',
            'page_sub_title',
            'type',
            'year'
        ];
        foreach ($keysToRemove as $keyToRemove) {
            if (array_key_exists($keyToRemove, $attributes)) {
                unset($attributes[$keyToRemove]);
            }
        }
        return $attributes;
    }
}