<?php 

/**
 * File Response
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category ApiSchool\V1
 * @package  ApiSchool\V1
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace ApiSchool\V1\Http; 

use \Psr\Http\Message\ResponseInterface;

class Response
{
    protected bool $success;
    protected int $statusCode;
    protected array $messages = [];
    protected array $data = [];
    protected array $headers = [];
    protected bool $toCache = false;
    
    /**
     * Set the value of $success
     */
    public function setSuccess(bool $success): self {
        $this->success = $success;
        return $this;
    }

     /**
     * Set the value of $statusCode
     * 
     * @return self 
     */
    public function setStatusCode(int $statusCode): self {
        $this->statusCode= $statusCode;
        return $this;
    }
    /**
     * Add
     * 
     * @return self 
     */
    public function setMessage(? string $message): self {
        $this->messages[]= $message;
        return $this;
    }
    /**
     * Set the value of $data
     */
    public function setData(array $data = []): self {
        $this->data = $data;
        return $this;
    }
    /**
     * Method setHeader
     *
     * @param string $key
     * @param string|null $value
     * 
     * @return self
     */
    public function setHeader(string $key, ?string $value): self {
        if (isset($key) && !array_key_exists($key, $this->headers)) {
            $this->headers[$key] = $value;
        }
        return $this;
    }

    /**
     * Get the value of _success
     *
     * @return bool
     */
    public function getSuccess(): bool {
        return $this->success;
    }

    /**
     * Get the value of _statusCode
     *
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }

   
    /**
     * Set the value of $toCache
     */
    public function setToCache(bool $toCache): self {
        $this->toCache = $toCache;
        return $this;
    }

    /**
     *  Get the value of $tocache
     */
     public function getToCache(): bool {
        return $this->toCache;
     }

    public function getHeader(string $key): ?string {
        return $this->headers[$key]?? null;
    }

    public function withHeaders(array $headers = []) {
        if (!$this->hasEmpty($headers) && array_is_list($headers)) {
            foreach ($headers as $key => $value) {
                $this->setHeader($key, $value);
            }
        }
        return $this;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function hasEmpty(array $data) {
        return count($data) > 0 ? false : true;
    }

    
    public function toArray(): array {
        return [
            'statusCode' => $this->statusCode,
            'success'    => $this->success,
            'messages'   => $this->messages,
            'data'       => $this->data
        ];
    }

    /**
     * Method Json
     *
     * @param int  $statusCode -
     * @param bool $success    -
     * @param ?string message  -
     * @param bool $toCache    - 
     * @param array $data      - 
     * @param int  $flags      - 
     * @param int  $depth      - 
     * 
     * @return mixed
     */
    public function json(
        int $statusCode=200, bool $success = false, 
        ?string $message = null, bool $toCache = false,
        mixed $data = null, int $flags = 0, int $depth = 512
    ) {
        $this->setStatusCode(statusCode:$statusCode)
        ->setSuccess(success:$success);
        
        if (!is_null($message)) $this->setMessage(message:$message);
        $this->setToCache(toCache:$toCache);
        if (!is_null($data)) $this->setData(data: $data);
        
        header('Content-type: application/json;charset=utf-8');
        if ($this->getToCache() == true) {
            header('Cache-control: max-age=60');
        } else {
            header('Cache-control: no-cache, no-store');
        }

       if (($this->getSuccess() !== false && $this->getSuccess() !== true)
            || !is_numeric($this->getStatusCode())
        ) {
           http_response_code(500);
           $this->setStatusCode(500);
           $this->setMessage('Erreur de création de réponse');
       } else {
            http_response_code($this->getStatusCode());
            if (!$this->hasEmpty($this->getHeaders())) {
                foreach ($this->getHeaders() as $key => $value) {
                    header(sprintf(" %s: %s", $key, $value));
               }
            }
       }
       echo json_encode($this->toArray(), $flags, $depth);
       exit;
    }
}