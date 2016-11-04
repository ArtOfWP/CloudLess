<?php
/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 2016-11-04
 * Time: 14:08
 */
namespace CLMVC\Controllers;


/**
 * Class BaseController
 * The base class to use for Controllers.
 *
 * @method onControllerPreInit
 * @method onControllerInit
 * @method notFound
 */
interface IController {
	/**
	 * Initiate the controller and execute filter.
	 */
	public function init();

	/**
	 * Executes an action on the controller.
	 *
	 * @param string $action The name of the action to execute.
	 * @param array $getParams values part of routing
	 *
	 * @throws \Exception Thrown if action is not found.
	 */
	public function executeAction( $action, $getParams = array() );
}
