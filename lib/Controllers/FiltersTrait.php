<?php
namespace CLMVC\Controllers;
use CLMVC\Interfaces\IFilter;

/**
 * Class FiltersTrait
 * @package CLMVC\Controllers
 */
trait FiltersTrait {
	/**
	 * @var [string][IFilter]
	 */
	private $filters;

	/**
	 * @param IController $controller
	 * @param $values
	 * @param $event
	 */
	protected function performForEvent( $controller, $values, $event ) {
		if (isset($this->filters[$event]) && is_array($this->filters[$event])) {
			foreach ($this->filters[$event] as $filter) {
				/**
				 * @var IFilter $filter
				 */
				$filter->perform($controller, $values);
			}
		}
	}

	/**
	 * @param $event
	 * @param $filter
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addFilter($event, $filter)
	{
		if (!method_exists($filter, 'perform')) {
			throw new \InvalidArgumentException('Supplied filter does not implement the required perform method.');
		}
		if (!isset($this->filters[$event])) {
			$this->filters[$event] = [];
		}
		$this->filters[$event][] = $filter;
	}

	/**
	 * @return mixed
	 */
	public function getAllFilters() {
		return $this->filters;
	}

	/**
	 * @param string $event
	 *
	 * @return array<IFilter>
	 */
	public function getFilterForEvent($event) {
		if (isset($this->filters[$event])) {
			return $this->filters[$event];
		}
		return [];
	}
}
