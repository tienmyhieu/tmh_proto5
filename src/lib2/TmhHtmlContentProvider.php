<?php

namespace lib2;

use lib\TmhJson;

class TmhHtmlContentProvider
{
    private TmhJson $json;
    private TmhNodeTreeTransformer $transformer;

    public function __construct(TmhJson $json, TmhNodeTreeTransformer $transformer)
    {
        $this->json = $json;
        $this->transformer = $transformer;
    }

    public function provideContent(string $route): string
    {
        $nodeTree = [];
        $content = 'content';

        if (0 == strlen($route)) {
            $content = 'portal_home';
            $nodeTree = $this->json->load(__DIR__ . '/../portal/', 'home');
//            echo "<pre>";
//            print_r($nodeTree);
//            echo "</pre>";
        }
        return $this->transformer->toHtml($content, $nodeTree);
    }

    public function providePortal(string $portal): string
    {
        $nodeTree = $this->json->load(__DIR__ . '/../portal/', $portal);
        return $this->transformer->toHtml('portal_' . $portal, $nodeTree);
    }
}
