<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['database'] = function () use ($app) {
  $db_url = $_ENV['DATABASE_URL'];
  $db_user = $_ENV['DATABASE_USER'];
  $db_pwd = $_ENV['DATABASE_PWD'];

  $pdo = new \Slim\PDO\Database(getenv('DATABASE_URL'), getenv('DATABASE_USER'), getenv('DATABASE_PWD'));
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  return $pdo;
};

$container['memcached'] = function () use ($app) {
  // create a new persistent client
  $m = new Memcached("memcached_pool");
  $m->setOption(Memcached::OPT_BINARY_PROTOCOL, TRUE);

  // some nicer default options
  $m->setOption(Memcached::OPT_NO_BLOCK, TRUE);
  $m->setOption(Memcached::OPT_AUTO_EJECT_HOSTS, TRUE);
  $m->setOption(Memcached::OPT_CONNECT_TIMEOUT, 2000);
  $m->setOption(Memcached::OPT_POLL_TIMEOUT, 2000);
  $m->setOption(Memcached::OPT_RETRY_TIMEOUT, 2);

  // setup authentication
  $username = getenv("MEMCACHEDCLOUD_USERNAME");
  $password = getenv("MEMCACHEDCLOUD_PASSWORD");
  if (!empty($username) && !empty($password)) {
    $m->setSaslAuthData($username, $password);
  }

  // We use a consistent connection to memcached, so only add in the
  // servers first time through otherwise we end up duplicating our
  // connections to the server.
  if (!$m->getServerList()) {
      // parse server config
      $servers = explode(",", getenv("MEMCACHEDCLOUD_SERVERS"));
      foreach ($servers as $s) {
          $parts = explode(":", $s);
          $m->addServer($parts[0], $parts[1]);
      }
  }

  return $m;
};
