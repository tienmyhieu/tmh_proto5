<?php

namespace lib;

class TmhContentController
{
    /** @var TmhApi|TmhHtml|TmhPdf */
    private $content;
    private TmhDataController $dataController;

    public function __construct(TmhApi $api, TmhDataController $dataController, TmhHtml $html, TmhPdf $pdf)
    {
        $this->dataController = $dataController;
        switch ($this->dataController->getContentType()) {
            case 'api':
                $this->content = $api;
                break;
            case 'pdf':
                $this->content = $pdf;
                break;
            default:
                $this->content = $html;
                break;
        }
    }

    public function renderContent(): void
    {
        $this->content->setHeader();
        $this->content->render($this->dataController);
    }
}