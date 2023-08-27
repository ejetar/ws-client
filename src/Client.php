<?php
namespace Ejetar\WsClient;

use Ejetar\WsClient\Payloads\RequestPayload;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;

class Client {
	public $loop;

	public function __construct(public $address, public bool $wait_for_response = true, public int $waitReplyUntil = 3) {

	}

	public function exit($conn) {
		$conn->close();
		$this->loop->stop();
	}

    public function __call($method, $params) {
        $this->loop = Factory::create();
        $connector = (new Connector($this->loop));

        $connector($this->address)
			->then(function (\Ratchet\Client\WebSocket $conn) use ($params, $method) {
				//Connected

				//Create payload
				$payload = new RequestPayload($method, $params[0]);

				//Timer
				$this->loop->addTimer($this->waitReplyUntil, function () use ($conn) {
					throw new \Exception("No response in $this->waitReplyUntil seconds");
				});

				$conn->on('message', function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn, $params, $payload, $method) {
					$response = json_decode($msg);
					if (isset($response->id) && $response->id == $payload->id) {
						//If there is NO an error, exit with success
						if (empty($response->error)) {
							$this->exit($conn);

						//If there is NO an error, throw it
						} else
							throw new \Exception($response->error);
					}
				});

				//Send payload
				$conn->send($payload);
				if ($this->wait_for_response === false)
					$this->exit($conn);

			}, function (\Exception $e) {
				//Could not connect
				$this->loop->stop();
			});

        $this->loop->run();
    }
}
