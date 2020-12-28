# ws-ratchet

This library provides a Ratchet based websocket adapter for the `k8s/client` library.

## General Use with the K8s library / Configuration Options

1. Install the library:

`composer require k8s/ws-ratchet`

**Note**: If you don't need to change any TLS settings, this is all that is needed.

2. If you need to configure any options, then you can use the below method to set those and use the websocket with them:

```php
use K8s\Client\K8s;
use K8s\Client\Options;
use K8s\WsRatchet\RatchetWebsocketAdapter;

$options = [
    # Set a timeout for the websocket connection
    'timeout' => 15,
    # Can toggle these to false if you are using a self-signed cert
    'tls' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
    ],
];

$websocket = new RatchetWebsocketAdapter($options);

# You can then pass the new websocket adapter in the options to be used
$options = new Options('k8s.endpoint.local');
$options->setWebsocketClient($websocket);

# Construct K8s to use the new websocket in the options
$k8s = new K8s($options);
```
