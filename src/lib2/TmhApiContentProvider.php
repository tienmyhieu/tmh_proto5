<?php

namespace lib2;

use lib\TmhJson;

class TmhApiContentProvider
{
    private TmhJson $json;

    public function __construct(TmhJson $json)
    {
        $this->json = $json;
    }

    public function request(string $entity, array $queries): array
    {
        $filteredEntities = $this->json->load(__DIR__ . '/../database/', $entity);
        $filteredEntities = $this->filterEntities($filteredEntities, 'active', '1');
        foreach ($queries as $query) {
            foreach ($query as $key => $value) {
                $filteredEntities = $this->filterEntities($filteredEntities, $key, $value);
            }
        }
        return $filteredEntities;
    }

    private function filterEntities(array $entities, string $key, string $value): array
    {
        return array_filter($entities, function($entity) use($key, $value) {
            $hasKey = in_array($key, array_keys($entity));
            return !$hasKey || $entity[$key] == $value;
        });
    }
}
