<?php
namespace Ejetar\WsClient;

use Ejetar\WsClient\Payloads\RequestPayload;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;

class Client {
	public function __construct(public $address, public $wait_for_response = true) {

	}

    public function __call($method, $params) {
        $loop = Factory::create();
        $connector = new Connector($loop);

        $connector($this->address)->then(function (\Ratchet\Client\WebSocket $conn) use ($loop, $params, $method) {
            //Connected
            $exit = function() use($conn, $loop) {
                $conn->close();
                $loop->stop();
            };

            $conn->on('message', function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn, $loop, $params, $method, $exit) {
                $exit();
            });

            $conn->send(new RequestPayload($method, $params[0]));
            if ($this->wait_for_response)
                $exit();

        }, function (\Exception $e) use ($loop) {
            //Could not connect
            $loop->stop();
        });

        $loop->run();
    }
}
