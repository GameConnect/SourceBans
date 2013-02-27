<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
	
	
	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param CAction $action the action to be executed.
	 */
	protected function beforeAction($action)
	{
		// Call onBeforeAction on SourceBans plugins
		foreach(SourceBans::app()->plugins as $plugin)
			$plugin->onBeforeAction($action);
		
		return parent::beforeAction($action);
	}
	
	/**
	 * This method is invoked at the beginning of {@link CController::render()}.
	 * You may override this method to do some preprocessing when rendering a view.
	 * @param string $view the view to be rendered
	 */
	protected function beforeRender($view)
	{
		// Call onBeforeRender on SourceBans plugins
		foreach(SourceBans::app()->plugins as $plugin)
			$plugin->onBeforeRender($view);
		
		return parent::beforeRender($view);
	}
	
	/**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * @param CAction $action the action just executed.
	 */
	protected function afterAction($action)
	{
		// Call onAfterAction on SourceBans plugins
		foreach(SourceBans::app()->plugins as $plugin)
			$plugin->onAfterAction($action);
		
		parent::afterAction($action);
	}
	
	/**
	 * This method is invoked after the specified is rendered by calling {@link CController::render()}.
	 * Note that this method is invoked BEFORE {@link CController::processOutput()}.
	 * You may override this method to do some postprocessing for the view rendering.
	 * @param string $view the view that has been rendered
	 * @param string $output the rendering result of the view. Note that this parameter is passed
	 * as a reference. That means you can modify it within this method.
	 */
	protected function afterRender($view, &$output)
	{
		// Call onAfterRender on SourceBans plugins
		foreach(SourceBans::app()->plugins as $plugin)
			$plugin->onAfterRender($view, $output);
		
		parent::afterRender($view, $output);
	}
}