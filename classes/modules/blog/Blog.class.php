<?php
/* ---------------------------------------------------------------------------
 * @Plugin Name: Treeblogs
 * @Plugin Id: Treeblogs
 * @Plugin URI:
 * @Description: Дерево блогов
 * @Author: mackovey@gmail.com
 * @Author URI: http://stfalcon.com
 * @LiveStreet Version: 0.4.2
 * @License: GNU GPL v2, http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * ----------------------------------------------------------------------------
 */

/**
 * Модуль Blog плагина Treeblogs
 */
class PluginTreeblogs_ModuleBlog extends PluginTreeblogs_Inherit_ModuleBlog
{
	/**
	 * Возвращаем блоги "родные братья" по дереву.
	 * BlogId - id одного из братьев
	 *
	 * @param int BlogId
	 * @return aBlogId|int
	 **/
	public function GetSibling ($BlogId)
	{
		$oBlog = $this->Blog_GetBlogsAdditionalData($BlogId);
		if (count($oBlog)==0) {
			return array();
		}
		$parentid= $oBlog[$BlogId]->getParentId();
		if (isset($parentid)) {
			$aBlogId = $this->oMapperBlog->GetSubBlogs($parentid);
		} else {
			$aBlogId = $this->oMapperBlog->GetMenuBlogs($this->User_GetUserCurrent()->getId());
		}
		return $aBlogId;
	}

	/**
	 * Строим ветку для блога
	 * 
	 * @param int BlogId
	 * @return array aBlogId
	 * */
	public function BuildBranch($BlogId)
	{
		if (false === ($res = $this->Cache_Get('blogs_tree_'.$BlogId))) {
			$res = array();
			array_unshift($res, $BlogId);
			$workid = $BlogId;
			while( true ){
				$workid = $this->oMapperBlog->getParentBlogId($workid);
				if ( !isset($workid) ){
					break;
				} else {
					array_unshift($res, $workid);
				}
			}
			$this->Cache_Set($res, 'blogs_tree_'.$BlogId, array(), 60 * 60 * 3);
		}
		return $res;
	}

	/**
	 * Строим дерево. 
	 * При $iParentId = null строим полное дерево. 
	 * Функция рекурсивна
	 * 
	 * @param int ParentId
	 * @return array 
	 * */
	public function buidlTree($iParentId = null){
		$aTree = array();
		if ($iParentId == null){
			/* Стартовая позиция, нулевой уровень, родителей нет. Пытаемся найти дерево в кеше */ 
			if (false === ($aTree = $this->Cache_Get("blogs_full_tree"))) {
				$aBlogsId = $this->oMapperBlog->GetMenuBlogs(0);
				$aoBlogs = $this->Blog_GetBlogsAdditionalData($aBlogsId);
				foreach ($aoBlogs as $oBlog){
					$aTree[$oBlog->getId()]['blog'] = $oBlog;
					$aTree[$oBlog->getId()]['child'] = $this->buidlTree($oBlog->getId());
				}
				$this->Cache_Set($aTree, "blogs_full_tree", array(), 60 * 60 * 3);
			}
			return $aTree;
		} else {
			/* Уровни имеющие родителя, уровень >= 1 */ 
			$aBlogsId = $this->oMapperBlog->GetSubBlogs($iParentId);
			$aoBlogs = $this->Blog_GetBlogsAdditionalData($aBlogsId);
			foreach ($aoBlogs as $oBlog){
				$aTree[$oBlog->getId()]['blog'] = $oBlog;
				$aTree[$oBlog->getId()]['child'] = $this->buidlTree($oBlog->getId());
			}
			return $aTree;
		}
	}
	
	/**
	 * Обновление связи блог-блог
	 *
	 * @param ModuleBlog_EntityBlog $oBlog
	 * @return boolean
	 */
	public function UpdateParentId($oBlog)
	{
		$this->Cache_Delete('blogs_parent_relations');
		/* чистим кеш полного дерева */
		$this->Cache_Delete('blogs_full_tree');
		/* подчищаем все деревья для топиков */
		$this->Cache_Clean( Zend_Cache::CLEANING_MODE_ALL, array('treeblogs.branches') );
		$aBlogsId = $this->oMapperBlog->GetMenuBlogs($this->User_GetUserCurrent()->getId());
		foreach ($aBlogsId as $blogId) {
			/* чистим кеш веток блогов */
			$this->Cache_Delete('blogs_tree_'.$blogId);
		}
		return $this->oMapperBlog->UpdateParentId($oBlog);
	}

	/**
	 * Получаем под-блоги
	 *
	 * @param int $blogId
	 * @return array
	 */
	public function GetSubBlogs($blogId)
	{
            return $this->oMapperBlog->GetSubBlogs($blogId);
	}

	/**
	 * Возвращает блоги для меню
	 *
         * @param boolean $bReturnIdOnly
         * @param boolean $bShowPersonal
	 * @return array
	 */
	public function GetMenuBlogs($bReturnIdOnly = false, $bShowPersonal=false)
	{
            $data = array();
            if ($bShowPersonal) {
		$data = $this->oMapperBlog->GetMenuBlogs($this->User_GetUserCurrent()->getId());
            } else {
		$data = $this->oMapperBlog->GetMenuBlogs(0);
            }
            
            if ($bReturnIdOnly) {/* Возвращаем только иденитификаторы */
                return $data;
            }
            return $this->Blog_GetBlogsAdditionalData($data);
	}

	/**
	 * Получаем блоги для выбора
	 * @param int $blogId
	 * @return array
	 */
	public function GetBlogsForSelect($blogId = null)
	{
		$aBlogSelect = array();
		$aBlogs = $this->oMapperBlog->GetBlogsForSelect($blogId);

		foreach ($aBlogs as $oBlog) {
			if (is_null($oBlog['parent_id'])) {
				array_push($aBlogSelect, $oBlog);
				if ($subBlogs = $this->_getSubBlogs($oBlog['id'], $aBlogs, 1)) {
					foreach ($subBlogs as $subBlog) {
						array_push($aBlogSelect, $subBlog);
					}
				}
			}
		}
		return $aBlogSelect;
	}

	/**
	 * Получаем подблоги
	 *
	 * @param int blogId
	 * @param array aBlogs
	 * @param int level
	 * @return array
	 */
	protected function _getSubBlogs($blogId, $aBlogs, $level)
	{
		$aBlogsSub = array();
		foreach ($aBlogs as $oBlog) {
			if ($oBlog['parent_id'] == $blogId) {
				$oBlog['title'] = str_repeat('&nbsp;-&nbsp;', $level) . $oBlog['title'];
				array_push($aBlogsSub, $oBlog);
				if ($subBlogs = $this->_getSubBlogs($oBlog['id'], $aBlogs, $level + 1)) {
					foreach ($subBlogs as $subBlog) {
						array_push($aBlogsSub, $subBlog);
					}
				}
			}
		}
		return $aBlogsSub;
	}

	/**
	 * Получаем главного родителя
	 * @param int $blogId
	 * @return array
	 */
	public function GetTopParentId($blogId)
	{
		if (false === ($aBlogs = $this->Cache_Get("blogs_parent_relations"))) {
			$aBlogs = $this->oMapperBlog->GetBlogRelations();
			$this->Cache_Set($aBlogs, "blogs_parent_relations", array(), 60 * 60 * 3);
		}
		return $this->_getTopId($aBlogs, $blogId);
	}

	/**
	 * Получаем родителя блога
	 *
	 * @param array $aBlogs
	 * @param int $blogId
	 * @return int
	 */
	protected function _getTopId($aBlogs, $blogId)
	{
		if (!array_key_exists($blogId, $aBlogs)) {
			return null;
		}
		if (is_null($aBlogs[$blogId])) {
			return $blogId;
		} else {
			return $this->_getTopId($aBlogs, $aBlogs[$blogId]);
		}
	}

}