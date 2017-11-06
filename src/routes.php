<?php

use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();

$container['database'] = function () use ($app) {
  $db_url = $_ENV['DATABASE_URL'];
  $db_user = $_ENV['DATABASE_USER'];
  $db_pwd = $_ENV['DATABASE_PWD'];

  $pdo = new \Slim\PDO\Database(getenv('DATABASE_URL'), getenv('DATABASE_USER'), getenv('DATABASE_PWD'));
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  return $pdo;
};

$app->get('/', function (Request $request, Response $response, array $args) {
  return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/products', function (Request $request, $response, $args) {
  $offset = intval($request->getQueryParam('offset', $default = '0'));
  $limit = intval($request->getQueryParam('limit', $default = '10'));
  $order = $request->getQueryParam('order', $default = 'asc'); # asc / desc
  $order_by = $request->getQueryParam('order_by', $default = 'id'); # id / price

  $stmt = $this->get('database')
          ->select()
          ->from('product_listing')
          ->limit($limit, $offset)
          ->execute();

  $result = array(
    'data' => $stmt->fetchAll(),
    'next_page' => ('/products?offset=' . ($offset + $limit) . "&limit=" . $limit)
  );

  return $response->withJson($result);
});
