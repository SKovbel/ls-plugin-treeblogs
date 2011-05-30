<?php

class PluginTreeblogs_ModuleTopic extends PluginTreeblogs_Inherit_ModuleTopic
{

	/**
	 * Список топиков по модифицированному фильтру
	 *
	 * @param  array $aFilter
	 * @param  int   $iPage
	 * @param  int   $iPerPage
	 * @return array
	 */
	public function GetTopicsByFilter($aFilter, $iPage = 0, $iPerPage = 0, $aAllowData = array('user' => array(), 'blog' => array('owner' => array()),
        'vote', 'favourite', 'comment_new')
	)
	{
		return parent::GetTopicsByFilter($this->_getModifiedFilterSubBlogs($aFilter),
		$iPage, $iPerPage, $aAllowData
		);
	}

	/**
	 * Количество топиков по фильтру
	 *
	 * @param array $aFilter
	 * @return integer
	 */
	public function GetCountTopicsByFilter($aFilter)
	{
		return parent::GetCountTopicsByFilter($this->_getModifiedFilterSubBlogs($aFilter));
	}

	/**
	 * Фильтр с дополнительными параметрами выборки для дерева блогов
	 *
	 * @param array $aFilter
	 * @return array
	 */
	protected function _getModifiedFilterSubBlogs(array $aFilter)
	{
		$subBlogsFilter = getRequest('b');
		$subBlogs = array();
		if ($subBlogsFilter) {
			if ($aSubBlogsUrl = explode(' ', $subBlogsFilter)) {
				foreach ($aSubBlogsUrl as $subBlogUrl) {
					if ($oBlog = $this->Blog_GetBlogByUrl($subBlogUrl)) {
						array_push($subBlogs, $oBlog->getId());
						$subBlogsMore = $this->_getSubBlogs($oBlog->getId());
						foreach ($subBlogsMore as $blogMore) {
							array_push($subBlogs, $blogMore);
						}
					}
				}
				$aFilter['blog_id'] = $subBlogs;
			}
		} elseif (isset($aFilter['blog_id'])) {
			if(!is_array($aFilter['blog_id'])) {
				$aFilter['blog_id']=array($aFilter['blog_id']);
			}
			$aBlogsId = $aFilter['blog_id'];
			foreach ($aFilter['blog_id'] as $blogId) {
				$subBlogs = $this->_getSubBlogs($blogId);
				$aBlogsId = array_merge($aBlogsId, $subBlogs);
			}
			$aFilter['blog_id'] = $aBlogsId;
		}

		if (isset($aFilter['blog_type'])){
			if (in_array('company', $aFilter['blog_type'])){
				$aFilter['blog_type'][] = 'open';
			}
		}

		return $aFilter;
	}

	/**
	 * Получаем под блоги
	 *
	 * @param int $blogId
	 * @return array
	 */
	protected function _getSubBlogs($blogId)
	{
		$blogIds = array();
		$blogIds = $this->Blog_GetSubBlogs($blogId);
		foreach ($blogIds as $blogId) {
			$subBlogIds = $this->_getSubBlogs($blogId);
			$blogIds = array_merge($blogIds, $subBlogIds);
		}
		return $blogIds;
	}

	/**
	 * Мержим деревья - удаления дублирующихся
	 * @param int $blogId
	 * @return array
	 */
	private function mergeTree($to, $from){
		$i=count($from)-1;
		foreach($from as $node) {
			if ( !isset($to[$node]) ){
				$to[$node]=$i;
			} else {
				if ($to[$node]==0 and $i > 0 ){
					$to[$node]=$i;
				}
			}
			$i--;
		}
		return $to;
	}

	/**
	 * Мержим  блоги (Добавление-удаление) для топика
	 * - Исключаем повторные вхождения блогов для топика
	 * 
	 * @param int $blogId
	 * @return array
	 */
	public function MergeTopicBlogs($topic_id, $defblog_id)
	{
		 
		$blogs_post = getRequest('subblog_id');
		$blogs_db   = $this->oMapperTopic->GetTopicSubBlogs($topic_id);

		$aResTree = array();
		$aTreeDefBlog = $this->Blog_BuildTreeBlogsFromTail($defblog_id);
		foreach ($aTreeDefBlog as $blog_id)
		{
			$aResTree[$blog_id] = 1;
		}

		foreach ($blogs_post as $blog_id)
		{
			$aTreeBlog = $this->Blog_BuildTreeBlogsFromTail($blog_id);
			$aResTree = $this->mergeTree($aResTree, $aTreeBlog);
		}
		$aResPosTree = array();
		foreach ($aResTree as $blog_id => $cnt) {
			if ($cnt == 0) {
				array_push($aResPosTree, $blog_id);
			}
		}

		foreach ($blogs_db as $blog)
		{
			if (!in_array($blog, $aResPosTree))
			{
				$this->oMapperTopic->DeleteTopicFromSubBlog($blog['blog_id'], $topic_id);
			}
		}

		foreach ($aResPosTree as $blog_id)
		{
			if (!in_array($blog_id, $blogs_db)  && $blog_id != -1)
			{
				$this->oMapperTopic->AddTopicToSubBlog($blog_id, $topic_id);
			}
		}
	}

	
	public function GetTopicSubBlogs($topic_id)
	{
		return $this->oMapperTopic->GetTopicSubBlogs($topic_id);
	}




}
