<?php

namespace Cjtaylor\Xmpp;

use Exception;
use Cjtaylor\Xmpp\Buffers\Response;
use Cjtaylor\Xmpp\Exceptions\DeadSocket;

class Socket
{
    public $connection;

    protected $responseBuffer;
    protected $options;

    /**
     * Socket constructor.
     * @throws DeadSocket
     */
    public function __construct(Options $options)
    {
        $this->responseBuffer = new Response();
        $this->connection = stream_socket_client($options->fullSocketAddress());

        if (!$this->isAlive($this->connection)) {
            throw new DeadSocket();
        }

        //stream_set_blocking($this->connection, true);
        stream_set_timeout($this->connection, 0, $options->getSocketTimeout());
        $this->options = $options;
    }

    public function disconnect()
    {
        fclose($this->connection);
    }

    /**
     * Sending XML stanzas to open socket
     * @param $xml
     */
    public function send(string $xml)
    {
        try {
            fwrite($this->connection, $xml);
            $this->options->getLogger()->logRequest(__METHOD__ . '::' . __LINE__ . " $xml");
            $this->checkSocketStatus();
        } catch (Exception $e) {
            $this->options->getLogger()->error(__METHOD__ . '::' . __LINE__ . " fwrite() failed " . $e->getMessage());
            return;
        }

        $this->receive();
    }

    public function receive()
    {
        $response = '';
        while ($out = fgets($this->connection)) {
            $response .= $out;
        }

        if (!$response) {
            return;
        }

        $this->responseBuffer->write($response);
        $this->options->getLogger()->logResponse(__METHOD__ . '::' . __LINE__ . " $response");
    }

    protected function isAlive($socket)
    {
        return $socket !== false;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getResponseBuffer(): Response
    {
        return $this->responseBuffer;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    protected function checkSocketStatus(): void
    {
        $status = socket_get_status($this->connection);

        //echo print_r($status);

        if ($status['eof']) {
            $this->options->getLogger()->error(
                __METHOD__ . '::' . __LINE__ .
                    " ---Probably a broken pipe, restart connection\n"
            );
        }
    }
}
