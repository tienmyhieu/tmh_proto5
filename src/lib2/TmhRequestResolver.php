<?php

namespace lib2;

class TmhRequestResolver
{
    private bool $hasLanguage = false;
    private bool $hasLocale = false;
    private bool $hasRoute = false;

    private string $portal = '';
    private string $route = '';

    private bool $showContent = true;
    private bool $showLanguages = false;
    private bool $showLocales = false;

    public function __construct()
    {
        $this->resolveSubDomain();
        $this->resolveRoute();
    }

    public function getPortal(): string
    {
        return $this->portal;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function hasRoute(): bool
    {
        return 0 < strlen($this->route);
    }

    public function showContent(): bool
    {
        return $this->showContent;
    }

    public function showLanguages(): bool
    {
        return $this->showLanguages;
    }

    public function showLocales(): bool
    {
        return $this->showLocales;
    }

    private function isInvalidSubDomain(string $subDomain): bool
    {
        $invalidSubDomains = [TMH, 'www'];
        return in_array($subDomain, $invalidSubDomains);
    }

    private function isChinese(string $subDomain): bool
    {
        return $subDomain == 'zh';
    }

    private function resolveRoute(): void
    {
        parse_str($_SERVER['REDIRECT_QUERY_STRING'], $fields);
        $this->route = $fields['title'];
    }

    private function resolveSubDomain(): void
    {
        $domain = $_SERVER['SERVER_NAME'];
        $domainParts = explode('.', $domain);
        $isInvalidSubDomain = $this->isInvalidSubDomain($domainParts[0]);
        if ($isInvalidSubDomain) {
            $this->portal = 'languages';
            $this->showContent = false;
            $this->showLanguages = true;
        } else {
            $isChinese = $this->isChinese($domainParts[0]);
            if ($isChinese) {
                $this->portal = $domainParts[0];
                $this->showContent = false;
                $this->showLocales = true;
            }
        }
    }
}
