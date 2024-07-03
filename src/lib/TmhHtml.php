<?php

namespace lib;

require_once(__DIR__ . '/TmhContent.php');

class TmhHtml extends TmhContent
{
    private array $components = [];
    private array $content = [];
    private array $entity;
    private array $innerHtml = [];
    private string $template;

    public function render(TmhDataController $dataController): void
    {
        $this->setEntity($dataController->getEntity());
        $this->setInnerHtml($dataController->getInnerHtml());
        $this->setTemplate($dataController);
        $this->setComponents();
//        echo "<pre>";
//        print_r($this->entity);
//        echo "</pre>";
        $pageTitle = $this->entity['title'];
        $pageDescription = '';
        $pageSubTitle = '';
        if (isset($this->entity['page_title'])) {
            $pageTitle = $this->entity['page_title'];
        }
        if (isset($this->entity['page_sub_title'])) {
            $pageSubTitle = $this->entity['page_sub_title'];
        }
        if (isset($this->entity['page_description'])) {
            $pageDescription = $this->entity['page_description'];
        }
        $content = str_replace('||contentTile||', $pageTitle, $this->components['content_title']);
        if ($pageSubTitle) {
            $content .= str_replace('||contentSubTitle||', $pageSubTitle, $this->components['content_sub_title']);
        }
        if ($pageDescription) {
            $content .= str_replace('||contentDescription||', $pageDescription, $this->components['content_description']);
        }
        $baseUrl = $dataController->getDomainUrl();
        foreach ($dataController->getContent() as $contentName => $contentSection) {
            switch ($contentName) {
                case 'route_groups':
                    $content .= $this->routeGroups($baseUrl, $contentSection);
                    break;
                case 'route_group_wrappers':
                    $content .= $this->routeGroupWrappers($baseUrl, $contentSection);
                    break;
                default:
                    $content .= $this->other($contentSection);
            }
        }
//        $content .= "<br/>" . $dataController->getEntityField('id');

        $this->content[] = $content;
        foreach ($this->content as $contentKey => $content) {
            echo str_replace('||content' . $contentKey . '||', $content, $this->template);
        }
    }

    public function setHeader()
    {
        header("Content-type:text/html; charset=UTF-8");
    }

    private function attributesToString(array $attributes): string{
        $attributesString = '';
        foreach ($attributes as $key => $value) {
            $attributesString .= $key .'="' . $value . '" ';
        }

        return ' ' . trim($attributesString);
    }

    private function other(array $other): string
    {
        return '';
    }

    private function routes(string $baseUrl, string $routeComponent, array $routes): string
    {
        $content = '';
        $id = $this->entity['id'];
        $replacements = in_array($id, array_keys($this->innerHtml)) ? $this->innerHtml[$id] : [];
        $useImagesTypes = ['emperor_coin1', 'emperor_coin2'];
        $useImages = in_array($this->entity['type'], $useImagesTypes);
        foreach ($routes as $primaryKey => $route) {
            $replacement = in_array($primaryKey, array_keys($replacements)) ? $replacements[$primaryKey] : null;
            $innerHtml = $replacement ?? $route['innerHtml'];
            $imageGroups = $route['image_groups'];
            $divClass = 'tmh_element_route';
            $hrefClass = 'tmh_a';
            if (count($imageGroups) && $useImages) {
                $specimenInfo = str_replace('||innerHtml||', $route['innerHtml'], $this->components['specimen_info']);
                $content .= $specimenInfo;
                $divClass = 'tmh_image_route';
                $hrefClass = 'tmh_a_img';
                $imgInnerHtml = '';
                $images = $imageGroups[0];
                foreach ($images as $image) {
                    $imageComponent = $this->components['image'];
                    $imageComponent = str_replace('||alt||', $route['title'], $imageComponent);
                    $imageComponent = str_replace('||src||', $image, $imageComponent);
                    $imgInnerHtml .= $imageComponent;
                }
                $innerHtml = $imgInnerHtml;
            }
            unset($route['image_groups']);
            $route = $this->minimizeAttributes($route);
            $route['href'] = $baseUrl . $route['href'];
            $tmpElementRoute = str_replace('||attributes||', $this->attributesToString($route), $routeComponent);
            $tmpElementRoute = str_replace('||innerHtml||', $innerHtml, $tmpElementRoute);
            $tmpElementRoute = str_replace('||hrefClass||', $hrefClass, $tmpElementRoute);
            $tmpElementRoute = str_replace('||divClass||', $divClass, $tmpElementRoute);
            $content .= $tmpElementRoute;
        }
//        $content .= $this->entity['id'];
        return $content;
    }

    private function routeGroups(string $baseUrl, array $entityRouteGroups): string
    {
        $content = '';
        foreach ($entityRouteGroups as $entityRouteGroup) {
            foreach ($entityRouteGroup as $routeGroup) {
                $content .= $this->routeGroup($baseUrl, $routeGroup, $this->components['element_route1']);
            }
        }
        return $content;
    }

    private function routeGroup(string $baseUrl, array $routeGroup, string $elementRoute): string
    {
        $content = '';
        $routeGroup = $this->minimizeAttributes($routeGroup);
        $content .= str_replace('||title||', $routeGroup['title'], $this->components['element_title']);
        $content .= $this->routes($baseUrl, $elementRoute, $routeGroup['routes']);
        return $content;
    }

    private function setComponents(): void
    {
        $dir = __DIR__ . '/../resources/component/html';
        $files = scandir($dir);
        foreach ($files as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }
            $this->components[$fileName] = file_get_contents($dir . '/' . $fileName);
        }
    }

    private function setEntity(array $entity)
    {
        $this->entity = $entity;
    }

    private function setInnerHtml(array $innerHtml)
    {
        $this->innerHtml = $innerHtml;
    }

    private function setTemplate(TmhDataController $dataController): void
    {
        $templateBaseUrl = __DIR__ . '/../resources/template/' . $dataController->getContentType() . '/';
        $templateFile = $templateBaseUrl . $dataController->getEntityField('type') . '.html';
        $this->template = file_get_contents($templateFile);
    }

    private function routeGroupWrappers(string $baseUrl, array $entityRouteGroupWrappers): string
    {
        $content = '';
        foreach ($entityRouteGroupWrappers as $entityRouteGroupWrapper) {
            $content .= str_replace('||title||', $entityRouteGroupWrapper['title'], $this->components['element_title']);
            $content .= $this->routeGroups($baseUrl, [$entityRouteGroupWrapper['route_groups']]);
        }
        return $content;
    }
}
