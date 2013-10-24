<?php
namespace nizsheanez\jsonRpc\traits;

trait Server {

    public $exception;
    public $result;
    public $message;

    public function __serialize()
    {
        $request = json_decode($this->message, true);

        $answer = array(
            'jsonrpc' => '2.0',
            'id' => isset($request['id']) ? $request['id'] : $this->newId(),
        );
        if ($this->exception) {
            if ($this->exception instanceof Exception) {
                $answer['error'] = $this->exception->getErrorAsArray();
            } else {
                $answer['error'] = [
                    'code' => self::INTERNAL_ERROR,
                    'message' => $this->exception
                ];
            }
        }
        if ($this->result) {
            $answer['result'] = $this->result;
        }

        if (self::isValidJsonRpc($answer)) {
            $answer['error'] = [
                'code' => self::INTERNAL_ERROR,
                'message' => 'Internal error'
            ];
        }

        return json_encode($answer);
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function isSuccessResponse()
    {
        return !$this->exception;
    }

    public function newId()
    {
        return md5(microtime());
    }

    public static function isValidJsonRpc($response)
    {
        $version = isset($response['jsonrpc']) && $response['jsonrpc'] == '2.0';
        $method = isset($response['method']);
        $data = isset($response['result']) || isset($response['error']);
        $additional = true;
        if (isset($response['error'])) {
            $additional = isset($response['error']['code'], $response['error']['message']);
        }
        return $version && $method && $data && $additional;
    }

}