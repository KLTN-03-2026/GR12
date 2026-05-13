<?php
$orders = \App\Models\Order::whereIn('status', ['preparing', 'ready', 'delivering'])->with(['user', 'shipper.user', 'items.product.user'])->get();
file_put_contents('test_output.json', json_encode($orders->toArray(), JSON_PRETTY_PRINT));
