<?php

namespace Berie;

use Berie\Router;

class Autoload
{
	private $config;
	private $module;
	private $unset = [
		'.', '..', '.DS_Store',
	];

	function __construct($config)
	{
		$this->config = include(getcwd() . $config);

		$this->__autoload();
		return new Router($this->module);
	}

	private function __autoload()
	{
		$modules 	= $this->config['modules'];

		foreach ($modules['path'] as $path) {
			$path = getcwd() . $path;

			foreach ($modules['module'] as $module) {
				$subPATH = $path . '/' . $this->toPATH($module);

				$this->loadDIR($subPATH);
			}
		}
	}

	private function isPHP($filename)
	{
		if(substr($filename, -10) == 'Module.php') {
			$config = isset($this->module) ?
				$this->module : [];

			$contentFILE = require($filename);

			$this->module = array_merge_recursive($config, $contentFILE);
		}

		return substr($filename, -4) == '.php' ?
			true : false;
	}

	private function toPATH($module)
	{
		return preg_replace('/\\\\/','/',$module);
	}

	private function loadDIR($dir, $depth = 0) {
		if($depth > 10) {
			return;
		}

		foreach (glob("$dir/*") as $path) {
			$this->isPHP($path) ?
				require($path) : $this->loadDIR($path, $depth + 1);
		}
	}
}
