<?php

namespace lib2;

use lib\TmhJson;

class TmhApiContentTransformer
{
    private array $dynamicNodes = [];
    private TmhJson $json;

    public function __construct(TmhJson $json)
    {
        $this->json = $json;
        $this->dynamicNodes = $this->json->load(__DIR__ . '/../dynamic/', 'node');;
    }

    public function transform(string $context, string $entity, array $entities): array
    {
        //echo "<pre>" . $entity . '_' . $context . PHP_EOL . "</pre>";
        switch ($entity . '_' . $context) {
            case 'language_portal_languages':
                return $this->transformLanguageList($entities);
            case 'locale_portal_zh':
                return $this->transformLocaleList($entities);
            default:
                return $entities;
        }
    }

    private function transformLanguageList(array $entities): array
    {
        $transformed = [];
        foreach ($entities as $primaryKey => $attributes) {
            $href = TMH_PROTOCOL . '://' . strtolower($primaryKey) . '.' . TMH . TMH_TLD;
            $a = $this->transformListLink($href, $attributes['native_name'], $attributes['native_name']);
            $transformed[] = $this->transformListItem([$a]);
        }
        return $transformed;
    }

    private function transformListItem(array $childNodes): array
    {
        $listItem = $this->dynamicNodes['8eljrvl9'];
        $listItem['childNodes'] = $childNodes;
        return $listItem;
    }

    private function transformListLink(string $href, string $innerHtml, string $title): array
    {
        $listLink = $this->dynamicNodes['xl8lgfar'];
        $listLink['attributes']['href'] = $href;
        $listLink['attributes']['title'] = $title;
        $listLink['innerHTML'] = $innerHtml;
        return $listLink;
    }

    private function transformLocaleList(array $entities): array
    {
        $transformed = [];
        foreach ($entities as $primaryKey => $attributes) {
            $href = TMH_PROTOCOL . '://' . strtolower($primaryKey) . '.' . TMH . TMH_TLD;
            $a = $this->transformListLink($href, $attributes['native_name'], $attributes['native_name']);
            $transformed[] = $this->transformListItem([$a]);
        }
        return $transformed;
    }
}