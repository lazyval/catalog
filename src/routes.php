<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response, array $args) {
  return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/products', function (Request $request, $response, $args) {
  $offset = intval($request->getQueryParam('offset', $default = '0'));
  $limit = intval($request->getQueryParam('limit', $default = '10'));
  $order = $request->getQueryParam('order', $default = 'asc'); # asc / desc
  $order_by = $request->getQueryParam('order_by', $default = 'id'); # id / price

  $memcached = $this->get('memcached');
  $ttl_sec = 3 * 60;
  $key = serializeFetchKey($offset, $limit, $order_by, $order);
  $cached_value = $memcached->get($key);
  $result = '';
  $no_cached_value = !$cached_value;
  if ($no_cached_value) {
    $stmt = $this->get('database')
            ->select()
            ->from('product_listing')
            ->limit($limit, $offset)
            ->orderBy($order_by, $order)
            ->execute();

    $result = json_encode(array(
      'data' => $stmt->fetchAll(),
      'next_page' => ('/products?offset=' . ($offset + $limit) . "&limit=" . $limit . "&order_by=" . $order_by . "&order=" . $order)
    ));
    $memcached->add($key, serialize($result), $ttl_sec);
  } else {
    $this->logger->debug("Serving request " . $key . " from cache");
    $result = unserialize($cached_value);
  }

  $resource_origin = ($no_cached_value ? 'database' : 'memcached');

  return $response
    ->write($result)
    ->withStatus(200)
    ->withHeader('X-Debug-Resource-Origin', $resource_origin)
    ->withHeader('Content-type', 'application/json');
});

$app->post('/products', function (Request $request, Response $response, array $args) {
  $body_json = json_decode($request->getBody(), true);

  $stmt = $this->get('database')
          ->insert(array('name', 'image_url', 'price', 'description'))
          ->into('product_listing')
          ->values(array($body_json['name'], $body_json['image_url'], $body_json['price'], $body_json['description']));

  $insertId = $stmt->execute(true);
  $this->get('memcached')->flush();

  return $response
    ->withAddedHeader('Location', '/products/' . $insertId)
    ->withStatus(201);
});

$app->put('/products/{id}', function (Request $request, Response $response, array $args) {
  $body_json = json_decode($request->getBody(), true);

  $stmt = $this->get('database')
    ->update(array(
      "name" => $body_json['name'],
      "image_url" => $body_json['image_url'],
      "price" => $body_json['price'],
      "description" => $body_json['description']))
    ->table('product_listing')
    ->where('id', '=', intval($args['id']));

    $affectedRows = $stmt->execute();
    if ($affectedRows > 0) {
        $this->get('memcached')->flush();
        return $response->withStatus(201);
    } else {
        return $response->withStatus(500);
    }
});

$app->delete('/products/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    $stmt = $this->get('database')
            ->delete()
            ->from('product_listing')
            ->where('id', '=', $id);

    $affectedRows = $stmt->execute();
    $this->logger->debug('Deleted rows ' . $affectedRows);
    $this->get('memcached')->flush();
    return $response->withStatus(200);
});
