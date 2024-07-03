<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use lib\TmhApi;
use lib\TmhContentController;
use lib\TmhDataController;
use lib\TmhHtml;
use lib\TmhJson;
use lib\TmhNodeController;
use lib\TmhPdf;

use lib2\TmhApiContentProvider;
use lib2\TmhApiContentTransformer;
use lib2\TmhHtmlContentProvider;
use lib2\TmhNodeTreeTransformer;
use lib2\TmhRequestResolver;

require_once(__DIR__ . '/defines.php');
require_once (__DIR__ . '/lib/TCPDF/tcpdf.php');
require_once(__DIR__ . '/lib/TmhApi.php');
require_once(__DIR__ . '/lib/TmhContent.php');
require_once(__DIR__ . '/lib/TmhContentController.php');
require_once(__DIR__ . '/lib/TmhDataController.php');
require_once(__DIR__ . '/lib/TmhHtml.php');
require_once(__DIR__ . '/lib/TmhJson.php');
require_once(__DIR__ . '/lib/TmhNodeController.php');
require_once(__DIR__ . '/lib/TmhPdf.php');

require_once(__DIR__ . '/lib2/TmhApiContentProvider.php');
require_once(__DIR__ . '/lib2/TmhApiContentTransformer.php');
require_once(__DIR__ . '/lib2/TmhHtmlContentProvider.php');
require_once(__DIR__ . '/lib2/TmhNodeTreeTransformer.php');
require_once(__DIR__ . '/lib2/TmhRequestResolver.php');

//require_once(__DIR__ . '/tmh_factory/src/TmhFactory.php');
//$factory =  new TmhFactory();
//$catalog = $factory->catalog();

$json = new TmhJson();
$apiContentProvider = new TmhApiContentProvider($json);
$apiContentTransformer = new TmhApiContentTransformer($json);
$nodeTreeTransformer = new TmhNodeTreeTransformer($apiContentProvider, $apiContentTransformer);
$requestResolver = new TmhRequestResolver();
$htmlContentProvider = new TmhHtmlContentProvider($json, $nodeTreeTransformer);

if (!$requestResolver->showContent()) {
    $content = $htmlContentProvider->providePortal($requestResolver->getPortal());
} else {
    $content = $htmlContentProvider->provideContent($requestResolver->getRoute());
}
echo $content;

//$json = new TmhJson();
//$dataController =  new TmhDataController($json);
//$tcPdf = new TCPDF();
//$api = new TmhApi();
//$html = new TmhHtml();
//$pdf = new TmhPdf($tcPdf);
//$nodeController = new TmhNodeController($dataController);
//$contentController = new TmhContentController($api, $dataController, $html, $pdf);
//$contentController->renderContent();
//echo "<pre>";
//echo $dataController->getEntityField('id') . PHP_EOL;
//echo "</pre>";
