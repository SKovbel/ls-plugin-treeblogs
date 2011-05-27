<?php

/**
 * Модуль Blog плагина Treeblogs
 */
class PluginTreeblogs_ModuleBlog extends PluginTreeblogs_Inherit_ModuleBlog
{
	/**
	 * Выбираем весь уровень, которому пренадлежит блог
	 * 
	 * @param int blogid
	 * @return array[blogid]
	 * 	 * */
	public function GetBlogsTreeLevel ($blogid)
	{
		$oBlog = $this->Blog_GetBlogsAdditionalData($blogid);
		if (count($oBlog)==0)
			return array();		
		$parentid= $oBlog[$blogid]->getParentId();
		if (isset($parentid))
			$res = $this->oMapperBlog->GetSubBlogs($parentid);
		else
			$res = $this->oMapperBlog->GetMenuBlogs();
  		return $res;
	}
	
	/**
	 * Строим дерево для блога
	 * @param int blogid
	 * @return aBlogId
	 * */
	public function BuildTreeBlogsFromTail($blogid)
	{
		if (false === ($res = $this->Cache_Get("blogs_tree_"+$blogid))) {
			$res = array();
			array_unshift($res, $blogid);
			$workid = $blogid;
			while( true ){
				$workid = $this->oMapperBlog->getParentBlogId($workid);
				if ( !isset($workid) ){
					break;
				} else {
					array_unshift($res, $workid);
				}
			}
			$this->Cache_Set($res, "blogs_tree_"+$blogid, array(), 60 * 60 * 3);
		}
		return $res;
	}

	/**
	 * Строим полное дерево для топика
	 * @param int blogid
	 * @return array[blogid]
	 * */
	public function GetTopicFullTree($oTopic){
		$aBlogsTopic = $this->Blog_BuildTreeBlogsFromTail($oTopic->getBlogId());

		$oBlogsTree=array();
		array_push($oBlogsTree, $this->Blog_GetBlogsAdditionalData($aBlogsTopic));
		$aSubBlogs	 = $this->Topic_GetTopicSubBlogs($oTopic->getId());

		foreach($aSubBlogs as $subblogid){
			$subBlog = $this->Blog_BuildTreeBlogsFromTail($subblogid);
			array_push($oBlogsTree, $this->Blog_GetBlogsAdditionalData($subBlog)); 
		}
		return 	$oBlogsTree;
    }

	/**
	 * Строим дерево для блога
	 * @param int blogid
	 * @return array[blogid]
	 * */	
   	public function buidlFullTree($parentid){
   		if (!$parentid){
			if (false === ($res = $this->Cache_Get("blogs_full_tree"))) {
				$res = array();
	   			$aBlogsId = $this->oMapperBlog->GetMenuBlogs();
	   			$aoBlogs = $this->Blog_GetBlogsAdditionalData($aBlogsId);
	   			foreach ($aoBlogs as $Blog){
	   				$res[$Blog->getId()]['url']		= $Blog->getUrlFull(); 
	   				$res[$Blog->getId()]['id']		= $Blog->getId(); 
	   				$res[$Blog->getId()]['title']	= $Blog->getTitle(); 
	   				$res[$Blog->getId()]['child']	= $this->buidlFullTree($Blog->getId()); 
	   			}
				$this->Cache_Set($res, "blogs_full_tree", array(), 60 * 60 * 3);
			}
   			return $res;
   		} else {
   			$res = array();
   			$aBlogsId = $this->oMapperBlog->GetSubBlogs($parentid);
	   		$aoBlogs = $this->Blog_GetBlogsAdditionalData($aBlogsId);
   			foreach ($aoBlogs as $Blog){
	   			$res[$Blog->getId()]['url']		= $Blog->getUrlFull(); 
	   			$res[$Blog->getId()]['id']		= $Blog->getId(); 
	   			$res[$Blog->getId()]['title']	= $Blog->getTitle(); 
	   			$res[$Blog->getId()]['child']	= $this->buidlFullTree($Blog->getId()); 
   			}
   			return $res;
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
		$this->Cache_Delete('blogs_full_tree');
 	   	$aBlogsId = $this->oMapperBlog->GetMenuBlogs();
		foreach ($aBlogsId as $blogId) {
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
	 * @return array
	 */
	public function GetMenuBlogs($bReturnIdOnly = false)
	{
		$data = $this->oMapperBlog->GetMenuBlogs();
		/**
		 * Возвращаем только иденитификаторы
		 */
		if ($bReturnIdOnly)
		return $data;

		$data = $this->Blog_GetBlogsAdditionalData($data);
		return $data;
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
	 * @param int $blogId
	 * @param array $aBlogs
	 * @param int $level
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