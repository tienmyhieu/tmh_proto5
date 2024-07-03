<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use lib\TmhApi;
use lib\TmhContentController;
use lib\TmhDataController;
use lib\TmhHtml;
use lib\TmhJson;
use lib\TmhPdf;

require_once(__DIR__ . '/defines.php');
require_once (__DIR__ . '/lib/TCPDF/tcpdf.php');
require_once(__DIR__ . '/lib/TmhApi.php');
require_once(__DIR__ . '/lib/TmhContent.php');
require_once(__DIR__ . '/lib/TmhContentController.php');
require_once(__DIR__ . '/lib/TmhDataController.php');
require_once(__DIR__ . '/lib/TmhHtml.php');
require_once(__DIR__ . '/lib/TmhJson.php');
require_once(__DIR__ . '/lib/TmhPdf.php');

$json = new TmhJson();
$dataController =  new TmhDataController($json);
$tcPdf = new TCPDF();
$api = new TmhApi();
$html = new TmhHtml();
$pdf = new TmhPdf($tcPdf);
$contentController = new TmhContentController($api, $dataController, $html, $pdf);
$contentController->renderContent();
//echo "<pre>";
//echo $dataController->getEntityField('id') . PHP_EOL;
//echo "</pre>";
