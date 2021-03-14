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

namespace K8s\WsRatchet;

use K8s\Core\Contract\ContextConfigInterface;
use K8s\Core\Contract\WebsocketClientFactoryInterface;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;

class AdapterFactory implements WebsocketClientFactoryInterface
{
    /**
     * @var array<string, mixed>
     */
    private $defaults;

    /**
     * @param array<string, mixed> $defaults Any default options to pass to the adapter.
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }

    /**
     * @inheritDoc
     */
    public function makeClient(ContextConfigInterface $fullContext): WebsocketClientInterface
    {
        $options = $this->defaults;

        if ($fullContext->getAuthType() === ContextConfigInterface::AUTH_TYPE_CERTIFICATE) {
            $options['tls']['local_cert'] = $fullContext->getClientCertificate();
            $options['tls']['local_pk'] = $fullContext->getClientKey();
        }
        if ($fullContext->getServerCertificateAuthority()) {
            $options['tls']['cafile'] = $fullContext->getServerCertificateAuthority();
        }

        return new RatchetWebsocketAdapter($options);
    }
}
