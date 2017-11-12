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
          ->orderBy($order_by, $order)
          ->execute();

  $result = array(
    'data' => $stmt->fetchAll(),
    'next_page' => ('/products?offset=' . ($offset + $limit) . "&limit=" . $limit . "&order_by=" . $order_by . "&order=" . $order)
  );

  return $response->withJson($result);
});

$app->post('/products', function (Request $request, Response $response, array $args) {
  $body_json = json_decode($request->getBody(), true);

  $stmt = $this->get('database')
          ->insert(array('name', 'image_url', 'price', 'description'))
          ->into('product_listing')
          ->values(array($body_json['name'], $body_json['image_url'], $body_json['price'], $body_json['description']));

  $insertId = $stmt->execute(true);

  return $response
    ->withAddedHeader('Location', '/products/' . $insertId)
    ->withStatus(201);
});

$app->delete('/products/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    $stmt = $this->get('database')
            ->delete()
            ->from('product_listing')
            ->where('id', '=', $id);

    $affectedRows = $stmt->execute();
    $this->logger->info('Deleted rows ' . $affectedRows);

    return $response->withStatus(200);
});
