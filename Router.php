<?php

namespace Berie;

class Router
{
	private $variables;
	private $routerMAP;
	private $routePART;
	private $routeHOME;
	private $requestMAP;
	private $internal;

	function __construct($moduleCFG)
	{
		$this->requestURI = $_SERVER['REQUEST_URI'];

		foreach ($moduleCFG["router"] as $name => $route) {
			$cutter = $this->cutter($route);

			if($cutter["isset"] === true) {
				$controller = $moduleCFG["controller"][$name];
				$event 		= !empty($this->routerMAP['event']) ?
					$this->routerMAP['event'] . "Event"  : 'index' . "Event" ;

				return (new $controller())->$event();
			}
		}

		var_dump(404); die;
		return 404;
	}

	public function cutter($route)
	{
		$return = [
			'isset' => false,
		];

		$this->routePART = $this->getRoutePART($route);
		$this->routeHOME = $this->routePART[0];

		$this->getRouterMAP();
		$this->getRequestMAP();

		foreach ($this->variables as $variable) {
			if($variable === $this->requestURI) {
				$return = [
					'isset' => true,
				];
			}
		}

		return $return;
	}

	private function getRoutePART($route)
	{
		return explode("[", preg_replace("/]/", "", $route['url']));
	}

	private function getRouterMAP()
	{
		$this->routerMAP 	= [];
		$this->variables 	= [$this->routeHOME];

		unset($this->routePART[0]);

		$this->routerMAP = array_merge($this->routerMAP,
			$this->hasController(),
			$this->hasEvent(),
			$this->hasParams()['sort'],
			$this->hasSlash()
		);

		$this->addSlash($this->routeHOME);
	}

	private function getRequestMAP()
	{
		$this->requestMAP = [];

		$this->cutURI = str_replace($this->routeHOME, "", $this->requestURI);
		$this->cutURI();
	}

	private function cutURI()
	{
		if(!empty($this->routerMAP['event'])
			&& !empty($this->routerMAP['param'])
		) {
			$this->cutURI = explode("/", $this->cutURI);
			unset($this->cutURI[0]);

			if(!empty($this->routerMAP['event'])
				&& !empty($this->cutURI)
			) {
				$this->cutURI = array_values($this->cutURI);

				$this->routerMAP['event'] = $this->cutURI[0];

				unset($this->cutURI[0]);

				$variable = $this->routeHOME . "/" . $this->routerMAP['event'];

				$this->variables[] = $variable;

				$this->addSlash($variable);
			} else {
				$this->routerMAP['event'] = null;
			}

			if(!empty($this->routerMAP['param'])
				&& !empty($this->cutURI)
			) {
				$this->cutURI = array_values($this->cutURI);
				$name = $this->routerMAP['param'];

				$this->internal['param'] = $this->hasParams()['values'][$name];

				$this->routerMAP['param'] = $this->cutURI[0];

				unset($this->cutURI[0]);

				$variable = $this->routeHOME
					. "/"
					. $this->routerMAP['event']
					. "/"
					. $this->routerMAP['param'];

				$this->variables[] = $variable;

				$this->addSlash($variable);
			} else {
				$this->routerMAP['param'] = null;
			}
		}
	}

	private function addSlash($route)
	{
		if(isset($this->routerMAP['slash'])) {
			$this->variables[] = $route . "/";
		}
	}

	private function hasController()
	{
		$return = [];
		$inData = array_search("/:controller", $this->routePART);

		if($inData) {
			$return['controller'] = $inData;
		}

		return $return;
	}

	private function hasEvent()
	{
		$return = [];
		$inData = array_search("/:event", $this->routePART);

		if($inData) {
			$return['event'] = $inData;
		}

		return $return;
	}

	public function hasParams()
	{
		$return = [
			'values' 	=> [],
			'sort'		=> [],
		];

		foreach ($this->routePART as $key => $value) {
			if($value !== '/:event'
				&& $value !== '/:controller'
			) {
				if(substr($value, 0, 2) === "/:") {
					$return['values'][$key] = $value;
					$return['sort']['param'] = $key;
				}
			}
		}

		return $return;
	}

	private function hasSlash()
	{
		$return = [];
		$inData = array_search("/", $this->routePART);

		if($inData) {
			$return['slash'] = $inData;
		}

		return $return;
	}
}
