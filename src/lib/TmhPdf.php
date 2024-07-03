<?php

namespace lib;

require_once(__DIR__ . '/TmhContent.php');

use TCPDF;

class TmhPdf extends TmhContent
{
    private const FONTS = ['en' => 'dejavusans', 'vi' => 'dejavusans',  'zh' => 'msungstdlight'];
    private const PER_PAGE = 3;

    private array $components = [];
    private array $entity;
    private array $pagedContent = [];
    private TCPDF $pdf;

    public function __construct(TCPDF $pdf)
    {
        $this->pdf = $pdf;
        $this->pdf->SetCreator(TMH_DOMAIN);
        $this->pdf->SetAuthor(TMH_DOMAIN);
    }

    public function render(TmhDataController $dataController): void
    {
        $this->setEntity($dataController->getEntity());
        $this->setComponents();
        $content = $dataController->getContent();
        $this->setPagedContent($content['route_groups']);
        $language = $dataController->getDomainField('language');
        $entityType = $this->entity['type'];
//        echo "<pre>";
//        echo $entityType . PHP_EOL;
//        print_r($this->entity);
//        print_r($content['route_groups']);
//        print_r($this->pagedContent);
//        echo "</pre>";
        $imageCellComponent = $this->components['image_cell'];
        $imageRowComponent = $this->components['image_row'];
        $spacerRowComponent = $this->components['spacer_row'];
        $tableComponent = $this->components['table'];
        $titleRowComponent = $this->components['title_row'];
        foreach ($this->pagedContent as $page => $routeGroups) {
            $this->pdf->SetMargins(10, 14, 10, true);
            $this->pdf->AddPage();
            $tableHtml = '';
            if ($page == 1) {
                $this->pdf->SetFont(self::FONTS[$language], '', 14);
                $this->pdf->writeHTML("<br/>" . $this->entity['title'] . "<br/>");
            }
            //$this->pdf->SetFont(self::FONTS[$language], '', 12);
            foreach ($routeGroups as $routeGroupTitle => $specimenGroups) {
                $tmpRouteGroupTitle = str_replace('||title||', $routeGroupTitle, $titleRowComponent);
                $tmpRouteGroupTitle = str_replace('||fontSize||', '10', $tmpRouteGroupTitle);
                $tableHtml .= $tmpRouteGroupTitle;
                foreach ($specimenGroups as $specimenGroupTitle => $specimenGroup) {
                    if (count($specimenGroup['images'])) {
                        $imageHtml = '';
                        foreach ($specimenGroup['images'] as $image) {
                            $image = str_replace('/128/', '/256/', $image);
                            $image = str_replace('.png', '.jpg', $image);
                            $imageHtml .= str_replace('||image||', $image, $imageCellComponent);
                        }
                        $tableHtml .= str_replace('||image_cells||', $imageHtml, $imageRowComponent);
                    } else {
                        $tableHtml .= $spacerRowComponent;
                        $tmSpecimenGroupTitle = str_replace('||title||', $specimenGroupTitle, $titleRowComponent);
                        $tmSpecimenGroupTitle = str_replace('||fontSize||', '6', $tmSpecimenGroupTitle);
                        $tableHtml .= $tmSpecimenGroupTitle;
                    }
                }
                $tableHtml .= $spacerRowComponent;
            }
            //echo str_replace('||rows||', $tableHtml, $tableComponent);
            $this->pdf->writeHTML(str_replace('||rows||', $tableHtml, $tableComponent), false);
        }
        $this->pdf->Output('tien_my_hieu.pdf');
    }

    public function setHeader()
    {
        header("Content-type:application/pdf");
    }

    private function setComponents(): void
    {
        $dir = __DIR__ . '/../resources/component/pdf';
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

    private function setPagedContent(array $routeGroups): void
    {
        $i = 0;
        $page = 1;
        foreach ($routeGroups as $routeGroup) {
            if ($i > 0 && $i % self::PER_PAGE == 0) {
                $page++;
            }
            //echo "<pre>" . $routeGroup['title'] . PHP_EOL . "</pre>";
            foreach ($routeGroup['routes'] as $route) {
                //echo "<pre>--" . $route['innerHtml'] . PHP_EOL . "</pre>";
                if (count( $route['image_groups'])) {
                    $images = $route['image_groups'][0];
                    foreach ($images as $image) {
                        $this->pagedContent[$page][$routeGroup['title']][$route['innerHtml']]['images'][] = $image;
                    }
                } else {
                    $this->pagedContent[$page][$routeGroup['title']][$route['innerHtml']]['images'] = [];
                }
                $i++;
            }
        }
    }
}
