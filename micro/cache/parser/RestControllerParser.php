<?php

namespace micro\cache\parser;

use micro\orm\parser\Reflexion;
use micro\cache\ClassUtils;

class RestControllerParser {
	private $controllerClass;
	private $resource;
	private $route;
	private $rest;
	private $authorizationMethods;

	public function __construct(){
		$this->rest=false;
		$this->authorizationMethods=[];
	}
	public function parse($controllerClass,$config) {
		$this->controllerClass=$controllerClass;
		$reflect=new \ReflectionClass($controllerClass);
		if (!$reflect->isAbstract() && $reflect->isSubclassOf("micro\\controllers\\rest\\RestController")) {
			$restAnnotsClass=Reflexion::getAnnotationClass($controllerClass, "@rest");
			if (\sizeof($restAnnotsClass) > 0){
				$routeAnnotsClass=Reflexion::getAnnotationClass($controllerClass, "@route");
				if(\sizeof($routeAnnotsClass)>0){
					$this->route=$routeAnnotsClass[0]->path;
				}
				$modelsNS=$config["mvcNS"]["models"];
				$this->resource=$modelsNS."\\".$restAnnotsClass[0]->resource;
				$this->rest=true;
				$methods=Reflexion::getMethods($controllerClass, \ReflectionMethod::IS_PUBLIC);
				foreach ( $methods as $method ) {
					$annots=Reflexion::getAnnotationsMethod($controllerClass, $method->name, "@authorization");
					if($annots!==false){
						$this->authorizationMethods[]=$method->name;
					}
				}
			}
		}
	}

	public function asArray() {
		return [ClassUtils::cleanClassname($this->controllerClass)=>["resource"=>ClassUtils::cleanClassname($this->resource),"authorizations"=>$this->authorizationMethods,"route"=>$this->route]];
	}

	public function isRest() {
		return $this->rest;
	}

}
