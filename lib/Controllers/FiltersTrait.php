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
	 * @param string $action
	 * @param null $result
	 */
	protected function performForEvent( $controller, $values, $event, $action = '', &$result = null ) {
		$filters = $this->getFiltersForEvent($event);
		foreach ($filters as $filter) {
			/**
			 * @var IFilter $filter
			 */
			$result = $filter->perform($controller, $values, $action);
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
	public function getFiltersForEvent($event) {
		if (isset($this->filters[$event])) {
			return (array)$this->filters[$event];
		}
		return [];
	}
}
