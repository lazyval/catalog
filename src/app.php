<?php

function serializeFetchKey($offset, $limit, $order_by, $order_direction) {
  return join('_', array($order_by, $order_direction, $offset, $offset + $limit));
}
