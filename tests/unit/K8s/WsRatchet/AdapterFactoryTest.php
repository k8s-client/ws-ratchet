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

use K8s\Core\Contract\ContextConfigInterface;
use K8s\WsRatchet\AdapterFactory;
use K8s\WsRatchet\RatchetWebsocketAdapter;

class AdapterFactoryTest extends TestCase
{
    /**
     * @var AdapterFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new AdapterFactory();
    }

    public function testItCanMakeTheAdapter(): void
    {
        $config = \Mockery::spy(ContextConfigInterface::class);
        $config->shouldReceive([
            'getClientCertificate' => '/client.crt',
            'getClientKey' => '/client.key',
            'getServerCertificateAuthority' => '/server.ca',
        ]);

        $result = $this->subject->makeClient($config);
        $this->assertInstanceOf(RatchetWebsocketAdapter::class, $result);
    }
}
