<?php

/**
 * MapImageForm is the data structure for uploading a map image.
 * It is used by the 'mapImage' action of 'GamesController'.
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * @package sourcebans.models
 * @since 2.0
 */
class MapImageForm extends CFormModel
{
	public $game_id;
	public $filename;

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('game_id', 'required'),
			array('filename', 'file', 'types'=>array('jpg')),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'game_id' => Yii::t('sourcebans', 'Game'),
			'filename' => Yii::t('sourcebans', 'Filename'),
		);
	}

	/**
	 * Saves the map image using the given values in the model.
	 * @return boolean whether save is successful
	 */
	public function save()
	{
		$this->filename = CUploadedFile::getInstance($this, 'filename');
		if(!$this->validate())
			return false;
		
		$game = SBGame::model()->findByPk($this->game_id);
		if($game === null)
			return false;
		
		return $this->filename->saveAs(Yii::getPathOfAlias('webroot.images.maps') . '/' . $game->folder . '/' . $this->filename);
	}
}
