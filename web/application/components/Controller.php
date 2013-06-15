<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @property string $title Returns the site title based on {@link breadcrumbs}. If those are not set it will return {@link pageTitle} instead.
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array header tab items. This property will be assigned to {@link CMenu::items}.
	 */
	public $tabs=array();
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
	 * Returns the site title based on {@link breadcrumbs}. If those are not set it will return {@link pageTitle} instead.
	 * 
	 * @return string the site title
	 */
	public function getTitle()
	{
		static $_data = '';
		if(empty($_data))
		{
			if(!empty($this->breadcrumbs))
			{
				$data  = array();
				foreach(array_reverse($this->breadcrumbs) as $key => $value)
					$data[] = !is_array($value) ? $value : $key;
				
				$_data = implode(Yii::app()->params['titleSeparator'], $data);
			}
			else
				$_data = $this->pageTitle;
		}
		
		return $_data;
	}
	
	
	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param CAction $action the action to be executed.
	 */
	protected function beforeAction($action)
	{
		SourceBans::app()->trigger('onBeforeAction', $action);
		
		return parent::beforeAction($action);
	}
	
	/**
	 * This method is invoked at the beginning of {@link CController::render()}.
	 * You may override this method to do some preprocessing when rendering a view.
	 * @param string $view the view to be rendered
	 */
	protected function beforeRender($view)
	{
		SourceBans::app()->trigger('onBeforeRender', $view);
		
		return parent::beforeRender($view);
	}
	
	/**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * @param CAction $action the action just executed.
	 */
	protected function afterAction($action)
	{
		SourceBans::app()->trigger('onAfterAction', $action);
		
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
		SourceBans::app()->trigger('onAfterRender', array($view, &$output));
		
		parent::afterRender($view, $output);
	}
}