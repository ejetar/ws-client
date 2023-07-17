<?php
namespace Ejetar\WsClient;

use Ejetar\WsClient\Payloads\RequestPayload;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;

class Client {
	public function __construct(public $address) {

	}

    public function __call($method, $params = null) {
        $loop = Factory::create();
        $connector = new Connector($loop);

        $connector($this->address)->then(function (\Ratchet\Client\WebSocket $conn) use ($loop, $params, $method) {
            //Connected

            $conn->on('message', function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn, $loop, $params, $method) {
				$conn->close();
                $loop->stop();
            });

            $conn->send(new RequestPayload($method, $params));

        }, function (\Exception $e) use ($loop) {
            //Could not connect
            $loop->stop();
        });

        $loop->run();
    }
}
