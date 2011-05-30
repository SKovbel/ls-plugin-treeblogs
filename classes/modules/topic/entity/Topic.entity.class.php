<?php

class PluginTreeblogs_ModuleTopic_EntityTopic extends PluginTreeblogs_Inherit_ModuleTopic_EntityTopic
{
	/**
	 * 
	 * */
	private $aBlogs;
	
	/**
	 * Строим множество всех блогов, которым прямо или косвенно принадлежит топик
	 * @return array aBlogId
	 * */
	public function GetBlogs(){
		if (!isset($this->aBlogs)){
			$this->aBlogs = $this->Blog_BuildTreeBlogsFromTail($this->getBlogId());
			$aSubBlogs	  = $this->Topic_GetTopicSubBlogs($this->getId());
			foreach($aSubBlogs as $subblogid){
				$subBlog = $this->Blog_BuildTreeBlogsFromTail($subblogid);
				$this->aBlogs = array_merge($this->aBlogs, $subBlog);
			}
		}
		return $this->aBlogs;
	}

	/**
	 * Возвращаем текущий блог взятый из url, в случая вхождение его в дерево блогов топика. 
	 * В случає отсутствии "родства" топика и блога - возвращаем дефолтный блог топика
	 *    
	 * @return oBlog
	 */
	public function getBlog(){
		if (Router::GetAction()=="blog"){
			$blogUrl = Router::GetActionEvent();
			$oBlog = $this->Blog_GetBlogByUrl($blogUrl);
			if (!empty($oBlog)) {
				if ( in_array($oBlog->getId(), $this->GetBlogs()) ){
					return $oBlog;
				}
			}

		}
		return parent::getBlog();
	}

}
