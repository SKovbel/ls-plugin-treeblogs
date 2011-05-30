<?php

/**
 * Плагин Treeblogs. Хуки для блогов
 */
class PluginTreeblogs_HookBlog extends Hook
{

	/**
	 * Регистрируем нужные хуки
	 *
	 * @return void
	 */
	public function RegisterHook()
	{
		$this->AddHook('template_form_add_blog_begin', 'TemplateFormAddBlogBegin', __CLASS__);
		$this->AddHook('template_menu_blog_begin', 'BlogMenu', __CLASS__);

		$this->AddHook('blog_add_after', 'BlogToBlog', __CLASS__);
		$this->AddHook('blog_edit_after', 'BlogToBlog', __CLASS__);

		$this->AddHook('blog_collective_show', 'BlogShow', __CLASS__, -100);
		$this->AddHook('topic_show', 'TopicShow', __CLASS__);
	}

	/**
	 * Шаблон с списком блогов. Цепляется на хук в начало формы редактирования блога
	 *
	 * @return string
	 */
	public function TemplateFormAddBlogBegin()
	{
		$blogId = $_REQUEST['blog_id'];
		$oBlog = $this->Blog_GetBlogById($blogId);
		$aBlogs = $this->Blog_GetBlogsForSelect($blogId);
		if ($oBlog) {
			unset($aBlogs[$blogId]);
			$this->Viewer_Assign('parentId', $oBlog->getParentId());
		}
		$this->Viewer_Assign('aBlogs', $aBlogs);
		return $this->Viewer_Fetch(Plugin::GetTemplatePath('treeblogs') . 'actions/ActionBlog/form_add_blog_to_blog.tpl');
	}

	/**
	 * Обновление связи блог-блог
	 *
	 * @param array $data
	 */
	public function BlogToBlog($data)
	{
		$oBlog = $data['oBlog'];
		$parentId = getRequest('parent_id');
		$oBlog->setParentId($parentId);
		$this->Blog_UpdateParentId($oBlog);
	}

	/**
	 * Выбираем блоги для меню
	 */
	public function BlogMenu()
	{
		$oBlogs = $this->Blog_GetMenuBlogs();
		$this->Viewer_Assign('oBlogsNav', $oBlogs);
	}

	/**
	 *  Добавляем подблоги
	 * @param array $aData
	 */
	public function BlogShow($aData)
	{
		$oBlog = $aData['oBlog'];
		$sShowType = $aData['sShowType'];
		$aBlogsId = $this->Blog_GetSubBlogs($oBlog->getId());
		$blogNavActive = $this->Blog_GetTopParentId($oBlog->getId());
		$aBlogs = $this->Blog_GetBlogsByArrayId($aBlogsId);
		$this->Viewer_Assign('aBlogsSub', $aBlogs);
		$this->Viewer_Assign('blogNavActive', $blogNavActive);
		$this->makePaging($oBlog, $sShowType);
		$aSort = explode(' ', getRequest('s'));
		$this->Viewer_Assign('aSort', $aSort);

		$aBlogFilter = explode(' ', getRequest('b'));
		$this->Viewer_Assign('aBlogFilter', $aBlogFilter);
		$this->Viewer_AppendScript( Plugin::GetTemplatePath(__CLASS__) . 'js/blog-filters.js');
		$this->Viewer_AppendStyle( Plugin::GetTemplatePath(__CLASS__) . 'css/blog-filters.css');
	}

	/**
	 *  Формируем пагинацию
	 * @param ModuleBlog_EntityBlog $oBlog
	 * @param string $sShowType
	 */
	protected function makePaging($oBlog, $sShowType)
	{
		$urlParams = '';
		if ($filters = $this->_getParamByName('filter')) {
			$urlParams = '/filter/' . $filters;
		}
		$getParams = array();
		getRequest('s') ? $getParams['s'] = getRequest('s') : true;
		getRequest('b') ? $getParams['b'] = getRequest('b') : true;
		$iPage = $this->GetPage(($sShowType == 'good') ? 0 : 1, 2) ? $this->GetPage(($sShowType == 'good') ? 0 : 1, 2) : 1;
		$aResult = $this->Topic_GetTopicsByBlog($oBlog, $iPage, Config::Get('module.topic.per_page'), $sShowType);
		$aPaging = ($sShowType == 'good') ? $this->Viewer_MakePaging($aResult['count'], $iPage, Config::Get('module.topic.per_page'), 4, rtrim($oBlog->getUrlFull() . $urlParams, '/'), $getParams) : $this->Viewer_MakePaging($aResult['count'], $iPage, Config::Get('module.topic.per_page'), 4, $oBlog->getUrlFull() . $sShowType . $urlParams, $getParams);
		$this->Viewer_Assign('aPaging', $aPaging);
	}

	/**
	 * Получаем страницу
	 *
	 * @param int $iParamNum
	 * @param int $iItem
	 * @return int|null
	 */
	protected function GetPage($iParamNum, $iItem=null)
	{
		$params = Router::GetParams();
		if (!isset($params[$iParamNum])) {
			return null;
		}
		if (!is_null($iItem)) {
			preg_match('/^(page(\d+))?$/i', $params[$iParamNum], $matches);
			if (isset($matches[$iItem])) {
				return $matches[$iItem];
			} else {
				return null;
			}
		} else {
			if (isset($params[$iParamNum])) {
				return $params[$iParamNum];
			} else {
				return null;
			}
		}
	}

	/**
	 * Получаем параметр из url
	 * @param string $name
	 * @return string|null
	 */
	protected function _getParamByName($name)
	{
		$params = Router::GetParams();
		if (in_array($name, $params)) {
			$item = array_search($name, $params);
			$key = Router::GetParam(++$item);
			return $key;
		}

		return null;
	}

	/**
	 * Подсвечиваем главного родителя
	 * @param array $aData
	 */
	public function TopicShow($aData)
	{
		$oTopic = $aData['oTopic'];
		$blogNavActive = $this->Blog_GetTopParentId($oTopic->getBlogId());
		$this->Viewer_Assign('blogNavActive', $blogNavActive);
	}

}