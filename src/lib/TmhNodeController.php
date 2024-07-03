<?php
namespace lib;

class TmhNodeController
{
    public const ANCESTOR_NODE = 'm2ef50vq';
    public const CHEVRON_NODE = 'sjcm479w';
    public const FIRST_NODE = 'ycn39toh';

    private TmhDataController $dataController;
    private array $apiData = [];
    private array $nodeTree = [];
    private array $nodes = [];

    public function __construct(TmhDataController $dataController)
    {
        $this->apiData = $dataController->getApiData();
        $this->dataController = $dataController;

        $this->nodes = $this->transformNodes($this->dataController->getTable('node'));
        $this->setNodeTree();
    }

    private function childNodes(array $primaryKeys): array
    {
        $converted = [];
        foreach ($primaryKeys as $primaryKey) {
            if (substr($primaryKey, 0, 2) != '||') {
                $node = $this->nodes[$primaryKey];
                $node['childNodes'] = $this->childNodes($node['childNodes']);
                $converted[] = $node;
            } else {
                $converted[] = $this->transformArray($primaryKey);
            }
        }
        return $converted;
    }

    private function setNodeTree()
    {
        //echo "<pre>";
        $firstNode = $this->nodes[self::FIRST_NODE];
        $firstNode['childNodes'] = $this->childNodes($firstNode['childNodes']);
        $this->nodeTree[self::FIRST_NODE] = $firstNode;
        //print_r($this->nodeTree);
        //echo "</pre>";
    }

    private function transformArray(string $attribute): array
    {
//        echo "<pre>";
        $transformedChildNodes = [];
        $replaced = str_replace('||', '', $attribute);
        $parts = explode('.', $replaced);
//        echo $parts[0] . ' ' . $parts[1] . PHP_EOL;
        if ($parts[0] == 'api') {
            $apiData = $this->apiData[$parts[1]];
//            print_r($apiData);
            if ($parts[1] == 'ancestors') {
                $ancestorsNode = $this->nodes[self::ANCESTOR_NODE];
                $chevronNode = $this->nodes[self::CHEVRON_NODE];
                foreach ($apiData as $ancestor) {
                    $ancestorsNode['attributes']['href'] = $ancestor['href'];
                    $ancestorsNode['attributes']['title'] = $ancestor['title'];
                    $ancestorsNode['innerHtml'] = $ancestor['innerHtml'];
                    $transformedChildNodes[] = $ancestorsNode;
                    $transformedChildNodes[] = $chevronNode;
                }
            }
        }
//        echo "</pre>";
        return $transformedChildNodes;
    }

    private function transformString(string $attribute): string
    {
        if (0 < strlen($attribute) && substr($attribute, 0, 2) == '||') {
            $replaced = str_replace('||', '', $attribute);
            $parts = explode('.', $replaced);
            if (2 == count($parts)) {
                if ($parts[0] == 'api') {
                    return $attribute;
                }
                $table = $this->dataController->getTable($parts[0]);
                if ($parts[0] == 'entity') {
                    $keyExists = in_array($parts[1], array_keys($table));
                    if ($keyExists) {
                        if (is_array($table[$parts[1]])) {
                            if ($parts[1] == 'meta_description') {
                                $attribute = implode(' ', $table[$parts[1]]);
                            }
                            if ($parts[1] == 'meta_keywords') {
                                $attribute = implode(', ', $table[$parts[1]]);
                            }
                        } else {
                            $attribute = $table[$parts[1]];
                        }
                    }
                }
                if ($parts[0] == 'current_domain') {
                    $attribute = $table[$parts[1]];
                }
            }
        }
        return $attribute;
    }

    private function transformNodes(array $nodes): array
    {
        $transformed = [];
        foreach ($nodes as $primaryKey => $node) {
            $node['innerHTML'] = $this->transformString($node['innerHTML']);
            $transformedAttributes = [];
            foreach ($node['attributes'] as $attributeName => $attributeValue) {
                $transformedAttributes[$attributeName] = $this->transformString($attributeValue);
            }
            $node['attributes'] = $transformedAttributes;
            $transformed[$primaryKey] = $node;
        }
        return $transformed;
    }
}