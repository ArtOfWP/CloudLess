<?php
/**
 * User: andreas
 * Date: 2011-12-21
 * Time: 22:58
*/
interface IIncludes
{
   function register( FrontInclude $include );
   function deregister($handle);
   function enqueue($location,FrontInclude $include);
   function dequeue($location,$handle);
   function isRegistered($handle);
   function isEnqueued($handle);
   function init();
}
