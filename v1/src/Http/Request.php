<?php

namespace App\SchoolManager\Http; 

use App\SchoolManager\Http\UploadedFile;

/**
 * Class Request
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category Assessment
 * @package  SchoolManager
 * @author   User: Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
class Request 
{
	/**
	 *  Uri
	 */
	protected ?string $uri;
	protected array $params = [];

	public function __construct()
	{
		$this->params = $_REQUEST;
	}
	public function setUri(?string $uri): void{
		$this->uri = $uri;
    }
	public function isMethod(string $method): bool {
     	return (mb_strtoupper($_SERVER['REQUEST_METHOD']) === $method);
    }
	public function isGet(): bool {
		return (mb_strtoupper($_SERVER['REQUEST_METHOD']) === 'GET');
    }
	public function isPOST(): bool {
		return (mb_strtoupper($_SERVER['REQUEST_METHOD']) === 'POST');
    }
	public function isDELETE(): bool {
		return (mb_strtoupper($_SERVER['REQUEST_METHOD']) === 'DELETE');
    }
	public function isPATCH(): bool {
		return (mb_strtoupper($_SERVER['REQUEST_METHOD']) === 'PATCH');
    }

	public function getMethod(): string{
		return mb_strtoupper($_SERVER['REQUEST_METHOD']);
	}

	public function matches(?string $route = null, string $pattern='#%s#') 
	{
		$subject = $_SERVER['REQUEST_URI'];
		if (preg_match(sprintf($pattern, $route), $subject, $matches)) {
            return $matches;
        }
        return false; 
	}

	public function isUri() 
	{
		$subject = $_SERVER['REQUEST_URI'];
		if (preg_match(sprintf('#%s#', $this->getUri()), $subject, $matches)) {
            return $matches;
        }
        return false; 
	}
	/**
	 * Method getHeaderLine
	 *
	 * @return string|null
	 */
	public function getHeaderLine(?string $name): ?string {
		return isset($_SERVER[$name])? $_SERVER[mb_strtoupper($name)] : null;
	}
	
	public function isJsonContentType(): bool {
		return ('application/json' === $this->getHeaderLine('CONTENT_TYPE'))?? false;
	}
	/**
	 * Get the value of uri
	 *
	 * @return ?string
	 */
	public function getUri(): ?string {
		return $this->uri;
	}

	public function uri(): ?string {
		return $_SERVER['REQUEST_URI']?? null;
	}

	public function getParams() {
		return $this->params;
	}

	public function getParam(string $name) : mixed {
		return isset($this->params[$name])? $this->params[$name] : false; 
	}
	public function hasParam(string $name) : bool {
		return $this->getParam(name: $name)?? false; 
	}

	public function contentJsonDecode(
		bool $use_include_path = false, mixed $context = null,
		int $offset = 0,int $length = null
	) : false|object {
		$contents = json_decode(
			file_get_contents(
				'php://input', $use_include_path, $context,
				 $offset, $length
				)
			);
		if (!$contents) {
			return false;
        }
		return $contents;
	}

	public function getUploadedFiles(): array 
	{
		$result = [];
		foreach($_FILES as $name => $arrayFiles) 
		{
			if (is_array($arrayFiles['name']) && count($arrayFiles['name'])>0) {
				for($index = 0; $index < count($arrayFiles['name']); $index++) {
					$uploadedFile = new UploadedFile(
						name: $arrayFiles['name'][$index],
						fullPath: $arrayFiles['full_path'][$index],
						type: $arrayFiles['type'][$index],
						tmpName: $arrayFiles['tmp_name'][$index],
						error: $arrayFiles['error'][$index],
						size: $arrayFiles['size'][$index]
					);
					$result[] = $uploadedFile;
				}
			} else {
				$result[] = new UploadedFile(
					name: $arrayFiles['name'],
					fullPath: $arrayFiles['full_path'],
					type: $arrayFiles['type'],
					tmpName: $arrayFiles['tmp_name'],
					error: $arrayFiles['error'],
					size: $arrayFiles['size']
				);
			} 
		}
		return $result;
	}
}