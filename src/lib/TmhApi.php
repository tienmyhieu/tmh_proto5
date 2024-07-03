<?php

namespace lib;

require_once(__DIR__ . '/TmhContent.php');

class TmhApi extends TmhContent
{
    private array $entity;

    public function render(TmhDataController $dataController): void
    {
        $this->setEntity($dataController->getEntity());
        $content = ['title' => $this->entity['title'], 'route_groups' => []];
        $baseUrl = $dataController->getDomainUrl();
        foreach ($dataController->getContent() as $contentName => $contentSection) {
            switch ($contentName) {
                case 'route_groups':
                    $content[$contentName] = $this->routeGroups($baseUrl, $contentSection);
                    break;
                default:
                    $content[$contentName] = $this->other($contentSection);
            }
        }
        echo json_encode($content);
    }
    public function setHeader()
    {
        header('Content-Type: application/json; charset=UTF-8');
    }

    private function other(array $other): array
    {
        return [];
    }

    private function routes(string $baseUrl, array $routes): array
    {
        $content = [];
        foreach ($routes as $primaryKey => $route) {
            $innerHtml = $route['innerHtml'];
            $attributes = $this->minimizeAttributes($route);
            $attributes['href'] = $baseUrl . $route['href'];
            $attributes['innerHtml'] = $innerHtml;
            $content[$primaryKey] = $attributes;
        }
        return $content;
    }

    private function routeGroups(string $baseUrl, array $routeGroups): array
    {
        $content = [];
        if (count($routeGroups)) {
            foreach ($routeGroups as $routeGroupPrimaryKey => $routeGroup) {
                $routeGroup = $this->minimizeAttributes($routeGroup);
                $routeGroup['routes'] = $this->routes($baseUrl, $routeGroup['routes']);
                $content[$routeGroupPrimaryKey] = $routeGroup;
            }
        }
        return $content;
    }

    private function setEntity(array $entity)
    {
        $this->entity = $entity;
    }
}
