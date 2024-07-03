<?php

namespace lib2;

class TmhNodeTreeTransformer
{
    private TmhApiContentProvider $apiContentProvider;
    private TmhApiContentTransformer $apiContentTransformer;
    private string $context;

    public function __construct(TmhApiContentProvider $apiContentProvider, TmhApiContentTransformer $apiContentTransformer)
    {
        $this->apiContentProvider = $apiContentProvider;
        $this->apiContentTransformer = $apiContentTransformer;
    }

    public function toHtml(string $context, array $nodeTree): string
    {
        $this->context = $context;
        return '<!DOCTYPE html>' . PHP_EOL . $this->nodes($nodeTree['childNodes']);
    }

    private function apiNodes(array $attributes): array
    {
        return $this->apiContentTransformer->transform(
            $this->context,
            $attributes['entity'],
            $this->apiContentProvider->request($attributes['entity'], $attributes['query'])
        );
    }

    private function attributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . $value . '"';
        }
        return $html;
    }

    private function childNodes(array $node, $eol=PHP_EOL): string
    {
        $closingHtml = $node['selfClosing'] ? '' : '>';
        return count($node['childNodes']) ? '>' . $eol . $this->nodes($node['childNodes']) : $closingHtml;
    }

    private function closeNode(array $node): string
    {
        $eol = in_array($node['htmlTag'], ['a', 'img']) ? '' : PHP_EOL;
        return ($node['selfClosing'] ? '/>' : '</' . $node['htmlTag']. '>') . $eol;
    }

    private function innerHtml(array $node): string
    {
        $eol = in_array($node['htmlTag'], ['a', 'img']) ? '' : PHP_EOL;
        return strlen($node['innerHTML']) > 0 ? '>' . $node['innerHTML'] : $this->childNodes($node, $eol);
    }

    private function nodes(array $nodes): string
    {
        $html = '';
        foreach ($nodes as $node) {
            if ($node['htmlTag'] == 'api') {
                $html .= $this->nodes($this->apiNodes($node['attributes']));
            } else {
                $html .= $this->openNode($node);
                $html .= $this->innerHtml($node);
                $html .= $this->closeNode($node);
            }
        }
        return $html;
    }

    private function openNode(array $node): string
    {
        return '<' . $node['htmlTag'] . $this->attributes($node['attributes']);
    }
}
