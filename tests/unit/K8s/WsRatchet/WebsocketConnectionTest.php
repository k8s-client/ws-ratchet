<?php

/**
 * This file is part of the k8s/ws-ratchet library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace unit\K8s\WsRatchet;

use K8s\WsRatchet\WebsocketConnection;
use Ratchet\Client\WebSocket;

class WebsocketConnectionTest extends TestCase
{
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|WebSocket
     */
    private $wsConn;

    /**
     * @var WebsocketConnection
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->wsConn = \Mockery::mock(WebSocket::class);
        $this->subject = new WebsocketConnection($this->wsConn);
    }

    public function testClose()
    {
        $this->wsConn->shouldReceive('close')->once();

        $this->subject->close();
    }

    public function testSend()
    {
        $this->wsConn->shouldReceive('send')
            ->with('foo');

        $this->subject->send('foo');
    }
}
