<?php

namespace lib;

class TmhDataController
{
    private array $ancestors = [];
    private array $apiData = [];
    private string $contentType = 'html';
    private array $database = [];
    private array $domain = [];
    private array $entity = [];
    private array $flattenedRoutes = [];
    private array $innerHtml = [];
    private TmhJson $json;
    private string $locale = 'vi-VN';
    private array $otherLanguageRoutes = [];
    private string $requestedRoute = '';

    public function __construct(TmhJson $json)
    {
        $this->json = $json;
        $this->loadDatabase();
        $this->setDomain();
        $this->setLocale();
        $this->loadLocales();
        $this->localizeDatabase();
        $this->transformDatabase();
        $this->setRequestedRouteAndContentType();
        $this->setOtherLanguageRoutes();
        $this->setFlattenedRoutes();
        $this->setEntity();
        $this->setAncestors();

//        echo "<pre>";
//        print_r($this->database['entity_route_group_wrapper']);
//        echo "</pre>";
    }

    public function getAncestors(): array
    {
        return $this->ancestors;
    }

    public function getApiData(): array
    {
        return $this->apiData;
    }

    public function getContent(): array
    {
        return [
            'route_groups' => $this->getEntityRouteGroups(),
            'route_group_wrappers' => $this->getEntityRouteGroupWrappers()
        ];
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getDomainField(string $field): string
    {
        return $this->domain[$field] ?? '';
    }

    public function getDomain(): array
    {
        return $this->domain;
    }

    public function getDomainUrl(): string
    {
        return TMH_PROTOCOL . '://' . $this->getDomainField('sub_domain') . TMH_DOMAIN;
    }

    public function getEntity(): array
    {
        return $this->entity;
    }

    public function getEntityField(string $field): string
    {
        return $this->entity[$field] ?? '';
    }

    public function getInnerHtml(): array
    {
        return $this->innerHtml;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getOtherLanguageRoutes(): array
    {
        return $this->otherLanguageRoutes;
    }

    public function getTable(string $tableName): array
    {
        return in_array($tableName, array_keys($this->database)) ? $this->database[$tableName] : [];
    }

    private function getEntityRouteGroups(): array
    {
        $entityId = $this->getEntityField('id');
        $tmpRouteGroups = [];
        if (0 == count($this->getEntityRouteGroupWrappers())) {
            foreach ($this->database['entity_route_group'] as $primaryKey => $entityRouteGroup) {
                if ($entityRouteGroup['entity'] == $entityId) {
                    foreach ($entityRouteGroup['route_groups'] as $tmpEntityRouteGroup) {
                        $tmpRouteGroups[$primaryKey][$tmpEntityRouteGroup] = $this->database['route_group'][$tmpEntityRouteGroup];
                    }
                }
            }
        }
        return $tmpRouteGroups;
    }

    private function getEntityRouteGroupWrappers(): array
    {
        $entityIType = $this->getEntityField('type');
        //echo "<pre>" . $entityIType . ' ' . 'getEntityRouteGroupWrappers' . PHP_EOL . "</pre>";
        $entityId = $this->getEntityField('id');
        $tmpRouteGroupWrappers = [];
        foreach ($this->database['entity_route_group_wrapper'] as $primaryKey => $entityRouteGroupWrapper) {
            if ($entityRouteGroupWrapper['entity'] == $entityId) {
                $tmpRouteGroupWrappers[$primaryKey] =$entityRouteGroupWrapper;
            }
        }
        return $tmpRouteGroupWrappers;
    }

    private function domainBaseUrl(array $domain): string
    {
        return TMH_PROTOCOL . '://' . $domain['sub_domain'] . TMH . TMH_TLD . '/';
    }

    private function setFlattenedRoutes(): void
    {
        $this->flattenedRoutes = [];
        foreach ($this->database['route'] as $primaryKey => $attributes) {
            $this->flattenedRoutes[$attributes['href']] = $primaryKey;
        }
    }

    private function localizeDatabase(): void
    {
        foreach ($this->localizations() as $table => $fields) {
            $tmpTable = $this->database[$table];
            foreach ($this->database[$table] as $primaryKey => $attributes) {
                foreach ($fields as $field => $localization) {
                    $sourceTable = $localization['table'];
                    $sourceField = $localization['field'];
                    if (is_array($attributes[$field])) {
                        $tmpField = [];
                        foreach ($attributes[$field] as $localizationField) {
                            $tmpField[] = $this->database[$sourceTable][$localizationField];
                        }
                        $attributes[$field] = $tmpField;
                    } else {
                        $targetIsNotBlank = 0 < strlen($attributes[$field]);
                        if (!$sourceField) {
                            if ($targetIsNotBlank) {
                                $attributes[$field] = $this->database[$sourceTable][$attributes[$field]];
                            }
                        } else {
                            if ($targetIsNotBlank) {
                                $attributes[$field] = $this->database[$sourceTable][$attributes[$field]][$sourceField];
                            }
                        }
                    }
                    $tmpTable[$primaryKey] = $attributes;
                }
            }
            $this->database[$table] = $tmpTable;
        }
    }

    private function localizations(): array
    {
        $localizations = [];
        foreach ($this->database['localization'] as $localization) {
            $localizations[$localization['destination_table']][$localization['destination_field']] = [
                'table' => $localization['source_table'],
                'field' => $localization['source_field']
            ];
        }
        return $localizations;
    }

    private function loadDatabase(): void
    {
        $files = scandir(__DIR__ . '/../database');
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fileName = str_replace('.json', '', $file);
            $this->database[$fileName] = $this->json->load(__DIR__ . '/../database/', $fileName);
        }
    }

    private function loadLocales(): void
    {
        $locales = $this->json->load(__DIR__ . '/../resources/locales/', $this->locale);
        $this->database['locale'] = $locales;
    }

    private function setAncestors(): void
    {
        $this->ancestors = [];
        if (0 < strlen($this->requestedRoute)) {
            $baseUrl = $this->domainBaseUrl($this->domain);
            $home = $this->database['route'][TMH_HOME_ROUTE];
            $this->ancestors[] = [
                'href' => rtrim($baseUrl, '/'),
                'innerHtml' => $home['innerHtml'],
                'title' => $home['title']
            ];
            $routeParts = explode('/', $this->requestedRoute);
            $tmpRoute = '';
            foreach ($routeParts as $routePart) {
                $tmpRoute .= $routePart;
                if ($tmpRoute != $this->requestedRoute) {
                    $routeKey = $this->flattenedRoutes[$tmpRoute];
                    $route = $this->database['route'][$routeKey];
                    $this->ancestors[] = [
                        'href' => $baseUrl . $route['href'],
                        'innerHtml' => $route['innerHtml'],
                        'title' => $route['title']
                    ];
                }
                $tmpRoute .= '/';
            }
        }
        $this->apiData['ancestors'] = $this->ancestors;
    }

    private function setDomain(): void
    {
        $domain = $_SERVER['SERVER_NAME'];
        $domains = $this->database['domain'];
        $domainExists = array_key_exists($domain, $domains);
        $this->domain = $domainExists ? $domains[$domain] : $domains[TMH . TMH_TLD];
        $this->domain['css'] = TMH_PROTOCOL . "://cdn.tienmyhieu.com/css/tmh-" . $this->domain['language'] . ".css";
        $this->domain['favicon'] = TMH_PROTOCOL . "://cdn.tienmyhieu.com/images/favicon.png";
        $this->database['current_domain'] = $this->domain;
    }

    private function setEntity(): void
    {
        $entityExists = array_key_exists($this->requestedRoute, $this->flattenedRoutes);
        $routeKey = $entityExists ? $this->requestedRoute : TMH_HOME_ROUTE;
        $entityId = $this->flattenedRoutes[$routeKey];
        $this->entity = $this->database['route'][$entityId];
        $this->entity['id'] = $entityId;
        $this->database['entity'] = $this->entity;
    }

    private function setLocale(): void
    {
        $this->locale = $this->domain['locale'];
    }

    private function setOtherLanguageRoutes(): void
    {
        $currentLocales = [];
        $otherLanguageRoutes = [];
        $activeDomains = array_filter($this->database['domain'], function($domain) {
            return $domain['active'] == '1' && $domain['locale'] != $this->locale;
        });
        if (0 < strlen($this->requestedRoute)) {
            $requestedRouteParts = explode('/', $this->requestedRoute);
            foreach ($requestedRouteParts as $requestedRoutePart) {
                foreach ($this->database['locale'] as $primaryKey => $locale) {
                    if ($locale == str_replace('_', ' ', $requestedRoutePart)) {
                        $currentLocales[] = $primaryKey;
                        break;
                    }
                }
            }
            foreach ($activeDomains as $activeDomain) {
                $tmpLocales = $this->json->load(__DIR__ . '/../resources/locales/', $activeDomain['locale']);
                $domainLocales = [];
                foreach ($currentLocales as $currentLocale) {
                    $domainLocales[] = str_replace(' ', '_', $tmpLocales[$currentLocale]);
                }
                $href = implode('/', $domainLocales);
                $otherLanguageRoutes[$activeDomain['locale']] = [
                    'href' => $this->domainBaseUrl($activeDomain) . $href,
                    'innerHtml' => $tmpLocales[$activeDomain['title']],
                    'title' => $this->database['locale'][$activeDomain['title']]
                ];
            }
        } else {
            foreach ($activeDomains as $activeDomain) {
                $otherLanguageRoutes[$activeDomain['locale']] = [
                    'href' => $this->domainBaseUrl($activeDomain),
                    'title' => $activeDomain['title']
                ];
            }
        }
        $this->otherLanguageRoutes = $otherLanguageRoutes;
    }

    private function setRequestedRouteAndContentType(): void
    {
        parse_str($_SERVER['REDIRECT_QUERY_STRING'], $fields);
        $this->requestedRoute = $fields['title'];
        $exploded = explode('/', $this->requestedRoute);
        if ($exploded[0] == 'api' || $exploded[0] == 'pdf') {
            $this->contentType = $exploded[0];
            unset($exploded[0]);
            $this->requestedRoute = implode('/', $exploded);
        }
    }

    private function transformDatabase(): void
    {
        $this->transformImageGroups();
        $this->transformRouteImageGroups();
        $this->transformRoutes();
        $this->transformRouteGroups();
        $this->transformRouteGroupWrappers();
        $this->transformInnerHtml();
    }

    private function transformImageGroups(): void
    {
        $tmpTable = [];
        foreach ($this->database['image_group'] as $primaryKey => $attributes) {
            $tmpImages = [];
            foreach($attributes['images'] as $image) {
                if ($image == '.') {
                    $image = TMH_SPACER_IMAGE;
                }
                $tmpImages[] = TMH_CDN . 'images/' . TMH_PREVIEW_SIZE . '/' . $image;
            }
            $attributes['images'] = $tmpImages;
            $tmpTable[$primaryKey] = $attributes;
        }
        $this->database['image_group'] = $tmpTable;
    }

    private function transformRouteImageGroups(): void
    {
        $tmpTable = [];
        foreach ($this->database['route_image_group'] as $primaryKey => $attributes) {
            $tmpImageGroups = [];
            foreach($attributes['image_groups'] as $imageGroup) {
                $tmpImageGroups[] = $this->database['image_group'][$imageGroup];
            }
            $attributes['image_groups'] = $tmpImageGroups;
            $tmpTable[$primaryKey] = $attributes;
        }
        $this->database['route_image_group'] = $tmpTable;
    }

    private function transformInnerHtml(): void
    {
        foreach ($this->database['inner_html'] as $innerHtml) {
            $this->innerHtml[$innerHtml['entity']][$innerHtml['route']] = $innerHtml['innerHtml'];
        }
    }

    private function transformRoutes(): void
    {
        $tmpTable = [];
        foreach ($this->database['route'] as $primaryKey => $attributes) {
            $attributes['href'] = implode('/', $attributes['href']);
            $attributes['href'] = str_replace('||id||', ' ' . $attributes['id'], $attributes['href']);
            $attributes['href'] = str_replace(' ', '_', $attributes['href']);
            $attributes['innerHtml'] = str_replace('||id||', ' ' . $attributes['id'],  $attributes['innerHtml']);
            $attributes['title'] = implode(' ', $attributes['title']);
            $attributes['title'] = str_replace('||id||', ' ' . $attributes['id'], $attributes['title']);
            $attributes['image_groups'] = [];
            if (in_array($primaryKey, array_keys($this->database['route_image_group']))) {
                $images = $this->database['route_image_group'][$primaryKey]['image_groups'][0]['images'];
                $attributes['image_groups'][] = $images;
            }
            if (in_array($primaryKey, array_keys($this->database['entity_metadata']))) {
                $entityMetadata = $this->database['entity_metadata'][$primaryKey];
                unset($entityMetadata['comment']);
                $attributes = array_merge($entityMetadata, $attributes);
            }
            $tmpTable[$primaryKey] = $attributes;
        }
        $this->database['route'] = $tmpTable;
    }

    private function transformRouteGroups(): void
    {
        $tmpTable = [];
        foreach ($this->database['route_group'] as $primaryKey => $attributes) {
            $attributes['title'] = implode(' ', $attributes['title']);
            $tmpTable[$primaryKey] = $attributes;
        }
        $this->database['route_group'] = $tmpTable;
        $tmpTable = [];
        foreach ($this->database['route_group'] as $primaryKey => $attributes) {
            $tmpRoutes = [];
            foreach ($attributes['routes'] as $route) {
                if ($this->database['route'][$route]['active'] == '1') {
                    $tmpRoutes[$route] = $this->database['route'][$route];
                }
            }
            $attributes['routes'] = $tmpRoutes;
            $tmpTable[$primaryKey] = $attributes;
        }
        $this->database['route_group'] = $tmpTable;
    }

    private function transformRouteGroupWrappers(): void
    {
        $tmpTable = [];
        foreach ($this->database['entity_route_group_wrapper'] as $primaryKey => $attributes) {
            $attributes['title'] = implode(' ', $attributes['title']);
            $tmpTable[$primaryKey] = $attributes;
        }
        $this->database['entity_route_group_wrapper'] = $tmpTable;
        $tmpTable = [];
        foreach ($this->database['entity_route_group_wrapper'] as $primaryKey => $attributes) {
            $tmpRouteGroups = [];
            foreach ($attributes['route_groups'] as $routeGroup) {
                if ($this->database['route_group'][$routeGroup]['active'] == '1') {
                    $tmpRouteGroups[$routeGroup] = $this->database['route_group'][$routeGroup];
                }
            }
            $attributes['route_groups'] = $tmpRouteGroups;
            $tmpTable[$primaryKey] = $attributes;
        }
        $this->database['entity_route_group_wrapper'] = $tmpTable;
    }
}
