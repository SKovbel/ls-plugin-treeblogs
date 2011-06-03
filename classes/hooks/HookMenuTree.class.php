<?php

/**
 * Класс генерирующий блок "Дерево Категорий"
 *
 */

class PluginTreeblogs_HookMenuTree extends Hook
{
	/**
	 * Регистрируем нужные хуки
	 *
	 * @return void
	 */
	public function RegisterHook()
	{
		$this->AddHook('topic_show',  'TreeMenuShow', __CLASS__);
		$this->AddHook('blog_collective_show', 'TreeMenuShow', __CLASS__);
		$this->AddHook('blog_show', 'TreeMenuShow', __CLASS__);
		$this->AddHook('personal_show', 'TreeMenuShow', __CLASS__);

		$this->AddHook('init_action', 'InitAction', __CLASS__);
	}

	/**
	 * Выводим блок - "дерево категорий". 
	 * @param array $aData
	 *
	 * @return void
	 */
	public function TreeMenuShow($aData)
	{
		/*Плагин выключен?*/
		if (Config::Get('plugin.treeblogs.treemenu_block_priority')==0)
		{
			return;
		}
		
		/*Подключаем css и js*/
		$this->Viewer_AppendStyle( Plugin::GetTemplatePath(__CLASS__) . 'css/tree-menu-block.css');
		$this->Viewer_AppendScript( Plugin::GetTemplatePath(__CLASS__) . 'js/tree-menu-block.js');
		
		/*Дерево целиком*/
		$this->Viewer_Assign('aTree',  $this->Blog_buidlTree() );
		
		if (isset($aData['oBlog'])  )
		{
			/*Просмотр блога, будет отмечена ветка текущего блога*/
			$iBlogId = $aData['oBlog']->getId();
		} 
		elseif (isset($aData['oTopic']))
		{
			/*Просмотр топика, будет отмечена ветка блога по умолчанию текущего топика*/
			$iBlogId = $aData['oTopic']->getBlog()->getId();
		} 

		if (isset($iBlogId))
		{
			/*ветка активного блога*/
			$this->Viewer_Assign('aTreePath',  $this->Blog_BuildBranch($iBlogId) );
			/*ативный блог*/
			$this->Viewer_Assign('iTreeBlogId',  $iBlogId );
		} else {
			/*Cтраница без активного блога (нпрм главная, персональный блг). Дерево закрыто*/
			$this->Viewer_Assign('aTreePath',  array() );
			$this->Viewer_Assign('iTreeBlogId',  -1 );
		}
		
		$this->Viewer_AddBlock('right',
			Plugin::GetTemplatePath(__CLASS__) .'actions/ActionMenuTree/treeMenuBlock.tpl',
			array(),
			Config::Get('plugin.treeblogs.treemenu_block_priority')
		);

	}

	/**
	 * Показываем блок "дерево категорий" для index страницы
	 * @param array $aVars
	 * 
	 * @return void
	 **/
	public function InitAction($aVars) 
	{
		$action = Router::GetActionEvent();
		if (empty($action))
		{
			$this->TreeMenuShow(array());
		}
	}
}


