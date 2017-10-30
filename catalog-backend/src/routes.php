<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response, array $args) {
  return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/products', function (Request $request, Response $response, array $args) {
  $this->logger->debug("Serving products from json file (yes, it's dumb for now)");
  $json_file = dirname(__FILE__).'/data.json';
  readfile($json_file);
  return $response->withHeader('Content-type', 'application/json');
});
