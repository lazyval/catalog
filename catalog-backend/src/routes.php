<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response, array $args) {
  return $this->renderer->render($response, 'index.phtml', $args);
});

function sort_by_price($a, $b) {
  if ($a['price'] == $b['price']) {
    return 0;
  }

  return ($a['price'] < $b['price']) ? -1 : 1;
}

function sort_by_id($a, $b) {
  if ($a['id'] == $b['id']) {
    return 0;
  }

  return ($a['id'] < $b['id']) ? -1 : 1;
}

function query_products($data, $offset, $limit, $order, $order_by) {
  $sorting_method = ($order_by == 'price') ? 'sort_by_price' : 'sort_by_id';
  usort($data, $sorting_method);
  if ($order == 'desc') {
    $data = array_reverse($data);
  }
  return array_slice($data, $offset, $limit);
}

$app->get('/products', function (Request $request, $response, $args) {
  $json_file = dirname(__FILE__).'/data.json';
  $this->logger->debug("Serving products from json file (yes, it's dumb for now)");

  $offset = intval($request->getQueryParam('offset', $default = '0'));
  $limit = intval($request->getQueryParam('limit', $default = '10'));
  $order = $request->getQueryParam('order', $default = 'asc'); # asc / desc
  $order_by = $request->getQueryParam('order_by', $default = 'id'); # id / price

  $json_data = file_get_contents($json_file);
  $json = json_decode($json_data, $assoc = true);

  $result = array(
    'data' => query_products($json, $offset, $limit, $order, $order_by),
    'next_page' => ('/products?offset=' . ($offset + $limit) . "&limit=" . $limit)
  );

  return $response->withJson($result);
});
