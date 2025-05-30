<?php 

/**
 * Router
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
namespace ApiSchool\V1\Attribute;

use \ApiSchool\V1\Attribute\RouteInterface;
use \ApiSchool\V1\Attribute\Route;
use \ApiSchool\V1\Exception\RouteException;
use \ApiSchool\V1\Http\Response;
use \ApiSchool\V1\Http\Request;

class Router 
{
    private array $_routes = [];
    private Route $_routeDefault;

    public function add(Route $routeObject, callable $callBack): void 
    {
        if ($routeObject instanceof Route && is_callable($callBack)) {
            if (!isset($this->_routes[$routeObject->getPath()])) {
                $this->_routes[$routeObject->getPath()] = [];
                  $this->_routes[$routeObject->getPath()][] =  [
                        'callback' => $callBack,
                        'route'   => $routeObject
                    ];
            } else {
                if ( $routeObject->getName() !== 'default') {
                    $this->_routes[$routeObject->getPath()][] =  [
                        'callback' => $callBack,
                        'route'   => $routeObject
                    ];
                }
            } 
        }
    }
    public function all(): array 
	{
        return$this->_routes;
    }
    
    public function defaultPath(): ?string 
	{
        return $this->_routeDefault?->getPath();
    }
    public function relativePath(?string $name= null): ?string 
	{
        $relativePath = null;
        if(is_null($name)) {
            $relativePath= $this->defaultPath();
        } else {
            $bag = $this->getBag($name);
            if (is_array($bag)) {
                $path = $bag['route']->getPath();
                $default = $this->defaultPath();
                $relativePath = "{$default}{$path}";
            }
        }
        return $relativePath;
    }

    private function getBag(?string $name = null): ?array 
    {
        $collects = $this->all();
        $bag = null;
        foreach ($collects as $collect) {
            if (is_array($collect) && count($collect) > 0) {
                foreach($collect as $c) {
                    if (isset($c['route']) && ($c['route']->getName() === $name)) {
                        $bag = $c;
                        break;
                    }
                }
            }
        }
        return $bag;
    }
    
    private function collect($reflection): void 
	{
        
        $className       = $reflection->getName();
        $classAttributes = $reflection->getAttributes(
            RouteInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
        $classMethods    = $reflection->getMethods(\ReflectionProperty::IS_PUBLIC);
		$instanceClass   = (new $className)?? null;
        // Attribute out
       foreach($classAttributes as $attribute) {
            if ($attribute->getName() === Route::class) {
                $route = $attribute->newInstance();
                $this->_routeDefault = $attribute->newInstance();
            }
        } 
        // Attribute innner
        foreach($classMethods as $classMethod) {
            $refMethod  = new \ReflectionMethod($classMethod->class, $classMethod->name);
            $attributes = $refMethod->getAttributes();
            $route = current(array_map(fn($attribute) => $attribute->newInstance(), $attributes));
            $closure = $reflection->getMethod($classMethod->name)
                    ->getClosure($instanceClass);
            $this->add(routeObject: $route, callBack: $closure);
        } 
    }

    public function initGlobales(array $controllers = []): void 
	{
        if (count($controllers) > 0) {
            foreach($controllers as $controller) {
                $this->collect(new \ReflectionClass($controller));
            }
        }
    }

    public function keyPathMatches(): string|bool{
        $request  = new Request;
        $keys = array_keys($this->all());
        $path   = $this->parse($request->uri());
        $returnKey = false;
        foreach ($keys as $key) {
            if (preg_match("#^".$key."$#", $path, $matches)) {
                $returnKey = $key;
                break;
            }
        }
        return $returnKey;
    } 

    public function parse($path): ?string 
    {
        $path= str_replace(
            $this->defaultPath(), '', 
            str_replace(ltrim($this->defaultPath(), "/"), '', $path)
        );
        $path= str_replace("//", '/', $path);
        return $path;
    }

    public function call(): mixed 
    {
        $response = new Response;
        $request  = new Request;
        $collects = $this->all();
        $method = $request->getMethod();

        if (!$keyRoute = $this->keyPathMatches()) {
            throw new RouteException('Endpoint not found',404);
        }
        $bags = isset($collects[$keyRoute])? $collects[$keyRoute] : [];
        $bag  = array_filter($bags, function($b) use($method) {
            return (isset( $b['route']) && $b['route']->getMethod() === $method);
         });
        $currentBag = (object) current($bag);
        if (array_key_exists($keyRoute,  $collects) && !isset($currentBag->route)) {
            throw new RouteException('Method not found',405);
        }
        echo call_user_func_array($currentBag->callback, [$request, $response]);
        exit;
    }
}