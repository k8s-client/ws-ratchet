<?php

/**
 * This file is part of the crs/k8s-ws-ratchet library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crs\K8sWsRatchet;

use Crs\K8s\Websocket\Contract\WebsocketConnectionInterface;
use Ratchet\Client\WebSocket;

class WebsocketConnection implements WebsocketConnectionInterface
{
    /**
     * @var WebSocket
     */
    private $connection;

    public function __construct(WebSocket $connection)
    {
        $this->connection = $connection;
    }

    public function close(): void
    {
        $this->connection->close();
    }

    public function send(string $data): void
    {
        $this->connection->send($data);
    }
}
