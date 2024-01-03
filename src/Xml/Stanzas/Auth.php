<?php

namespace Cjtaylor\Xmpp\Xml\Stanzas;

use Cjtaylor\Xmpp\AuthTypes\Authenticable;

class Auth extends Stanza
{
    public function authenticate()
    {
        $response = $this->socket->getResponseBuffer()->read();
        $options = $this->socket->getOptions();

        $tlsSupported = self::isTlsSupported($response);
        $tlsRequired = self::isTlsRequired($response);

        if ($tlsSupported && ($tlsRequired || (!$tlsRequired && $options->usingTls()))) {
            $this->startTls();
            $this->socket->send(self::openXmlStream($options->getHost()));
        }

        $xml = $this->generateAuthXml($options->getAuthType());
        $this->socket->send($xml);
        $this->socket->send(self::openXmlStream($options->getHost()));
    }

    protected function startTls()
    {
        $this->socket->send("<starttls xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>");
        $response = $this->socket->getResponseBuffer()->read();

        if (!self::canProceed($response)) {
            $this->socket->getOptions()->getLogger()->error(__METHOD__ . '::' . __LINE__ .
                " TLS authentication failed. Trying to continue but will most likely fail.");
            return;
        }

        stream_socket_enable_crypto($this->socket->connection, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    }

    protected function generateAuthXml(Authenticable $authType): string
    {
        $mechanism = $authType->getName();
        $encodedCredentials = $authType->encodedCredentials();
        $nameSpace = "urn:ietf:params:xml:ns:xmpp-sasl";

        return "<auth xmlns='{$nameSpace}' mechanism='{$mechanism}'>{$encodedCredentials}</auth>";
    }
}
