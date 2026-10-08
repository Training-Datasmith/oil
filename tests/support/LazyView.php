<?php

namespace Tests\Support;

class LazyView
{
	protected $name;
	protected $data = array();
	public $actions;

	public function __construct($name, array $data = array())
	{
		$this->name = $name;
		$this->data = $data;
	}

	public function __set($key, $value)
	{
		$this->data[$key] = $value;
	}

	public function __get($key)
	{
		return $this->data[$key] ?? null;
	}

	public function __toString()
	{
		$data = $this->data;
		if (isset($this->actions))
		{
			$data['actions'] = $this->actions;
		}
		$app = APPPATH.'views/'.$this->name.'.php';
		if (is_file($app))
		{
			return $this->renderFile($app, $data);
		}
		$oil = OIL_ROOT.DS.'views'.DS.str_replace('/', DS, $this->name).'.php';
		if (is_file($oil))
		{
			return $this->renderFile($oil, $data);
		}
		throw new \RuntimeException('View not found: '.$this->name);
	}

	protected function renderFile($path, array $data)
	{
		extract($data, EXTR_SKIP);
		ob_start();
		include $path;
		return ob_get_clean();
	}
}
