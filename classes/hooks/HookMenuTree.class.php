
<?php

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
	 * Генерим и показываем блок - древовидное меню блогов
	 * @param array $aData
	 */
	public function TreeMenuShow($aData)
	{
		if (isset($aData['oBlog'])  ){
			$currblog_id = $aData['oBlog']->getId();
		} elseif (isset($aData['oTopic'])){
			$oBlog = $aData['oTopic']->getBlog();
			$currblog_id = $oBlog->getId();
		}

		if (isset($currblog_id)){
			$treePath = $this->Blog_BuildTreeBlogsFromTail($currblog_id);
			$this->Viewer_Assign('aTreePath',  $treePath );
			$this->Viewer_Assign('currTreePath',  $currblog_id );
		} else {
			$this->Viewer_Assign('aTreePath',  array() );
			$this->Viewer_Assign('currTreePath',  -1 );
		}
			
		$fulltree = $this->Blog_buidlFullTree(null);
		$this->Viewer_Assign('aFullTree',  $fulltree );
			
		$this->Viewer_Assign('side_bar_level', Plugin::GetTemplatePath('treeblogs') .'actions/ActionMenuTree/side_bar_level.tpl');
		$this->Viewer_AddBlock('right', 
								Plugin::GetTemplatePath('treeblogs') .'actions/ActionMenuTree/side_bar.tpl',
								array(),
								Config::Get('plugin.treeblogs.treemenu_block_priority')
		);

	}

	/**
	 * 1. Показываем древовидное меню только для главной страници
	 * 2. Подключаем front.css и blog-menu.js
	 * @param array $aVars
	 **/
	public function InitAction($aVars) {
		$action = Router::GetActionEvent();
		if (empty($action)){
			$this->TreeMenuShow(array());
		}
		$this->Viewer_AppendStyle( Plugin::GetTemplatePath(__CLASS__) . 'css/front.css');
		$this->Viewer_AppendScript( Plugin::GetTemplatePath(__CLASS__) . 'js/blog-menu.js');

	}
}


