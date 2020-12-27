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

use Crs\K8s\Websocket\Contract\FrameHandlerInterface;
use Crs\K8s\Websocket\Contract\WebsocketClientInterface;
use Crs\K8s\Websocket\Exception\WebsocketException;
use Crs\K8s\Websocket\Frame;
use Psr\Http\Message\RequestInterface;
use Ratchet\Client\Connector as RatchetConnector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;

class RatchetWebsocketAdapter implements WebsocketClientInterface
{
    private const DEFAULT_SOCKET_OPTIONS = [
        'timeout' => 15,
        'tls' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ];

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var array
     */
    private $socketOptions;

    public function __construct(
        array $socketOptions = [],
        ?ConnectorInterface $connector = null,
        ?LoopInterface $loop = null
    ) {
        $this->loop = $loop ?? Factory::create();
        $this->socketOptions = empty($socketOptions) ? self::DEFAULT_SOCKET_OPTIONS : $socketOptions;
        $this->connector = $connector ?? new Connector($this->loop, $this->socketOptions);
    }

    public function connect(
        string $subprotocol,
        RequestInterface $request,
        FrameHandlerInterface $payloadHandler
    ): void {
        $wsConnection = null;
        $exception = null;
        $uri = $request->getUri();
        $connector = new RatchetConnector($this->loop, $this->connector);

        try {
            $promise = call_user_func(
                $connector,
                (string)$uri,
                [$subprotocol],
                $request->getHeaders()
            );

            $promise->then(function (WebSocket $conn) use (&$wsConnection, $payloadHandler) {
                $wsConnection = $conn;
                $clientConn = new WebsocketConnection($conn);

                $payloadHandler->onConnect($clientConn);
                $conn->on('close', function () use ($payloadHandler) {
                    $payloadHandler->onClose();
                });
                $conn->on('message', function (MessageInterface $message) use ($clientConn, $payloadHandler) {
                    $payloadHandler->onReceive(
                        new Frame(
                            $message->getOpcode(),
                            $message->getPayloadLength(),
                            $message->getPayload()
                        ),
                        $clientConn
                    );
                });
            }, function (\Exception $e) use (&$exception) {
                $exception = new WebsocketException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );

                return $exception;
            });

            $this->loop->run();
        } catch (\Exception $e) {
            throw new WebsocketException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        if ($wsConnection !== null) {
            $wsConnection->close();
        }

        # Is this correct? Can't seem to throw it in the promise.
        if ($exception !== null) {
            throw $exception;
        }
    }
}
