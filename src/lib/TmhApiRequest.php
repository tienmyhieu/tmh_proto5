<?php

namespace lib;

class TmhApiRequest
{
    private string $mimeType;
    private string $requestedRoute;

    public function __construct()
    {
        $this->resolveRequest();
    }

    public function getContentType(): string
    {
        return "Content-type:" . $this->mimeType;
    }

    public function getRequestedRoute(): string
    {
        return $this->requestedRoute;
    }

    private function resolveRequest(): void
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

    private function setMimeType(string $contentType): void
    {
        switch ($contentType) {
            case 'api':
                $this->mimeType = 'application/json; charset=utf-8';
                break;
            case 'pdf':
                $this->mimeType = 'application/pdf; charset=utf-8';
                break;
            default:
                $this->mimeType = 'text/html; charset=utf-8';
                break;
        }
    }
}