<?php
use lib\TmhApiRequest;

require_once(__DIR__ . '/../lib/TmhApiRequest.php');

$request = new TmhApiRequest();
echo '<pre>';
echo $request->getRequestedRoute();
echo '</pre>';
