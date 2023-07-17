<?php
namespace Ejetar\WsClient\Payloads;

class ResponsePayload {
    public $jsonrpc = "2.0";
    public $id;

    public function __construct(public $result, public $error = null) {
        $this->id = time();
    }

    public function __toString() {
        return json_encode([
            "jsonrpc" => $this->jsonrpc,
            "result"  => $this->result,
            "error"   => $this->error,
            "id"      => time(),
        ]);
    }
}
