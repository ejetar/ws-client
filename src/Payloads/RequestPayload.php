<?php
namespace Ejetar\WsClient\Payloads;

class RequestPayload {
    public $jsonrpc = "2.0";
    public $id;

    public function __construct(public $method, public $params) {
        $this->id = time();
    }

    public function __toString() {
        return json_encode([
            "jsonrpc" => $this->jsonrpc,
            "method"  => $this->method,
            "params"  => $this->params,
            "id"      => time(),
        ]);
    }
}
