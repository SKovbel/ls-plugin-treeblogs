<?php

class PluginTreeblogs_ModuleTopic_EntityTopic extends PluginTreeblogs_Inherit_ModuleTopic_EntityTopic
{
	/**
	 * 
	 * */
	private $aBlogs;
	
	/**
	 * Возвращаем блоги для топика
	 * как непосредственно связанные так и имеющие родство
	 * 
	 * @return array aBlogId
	 * */
	public function GetBlogs(){
		if (!isset($this->aBlogs)){
			$this->aBlogs = $this->Blog_BuildBranch($this->getBlogId());
			$aSubBlogs	  = $this->Topic_GetTopicSubBlogs($this->getId());
			foreach($aSubBlogs as $subblogid){
				$subBlog = $this->Blog_BuildBranch($subblogid);
				$this->aBlogs = array_merge($this->aBlogs, $subBlog);
			}
		}
		return $this->aBlogs;
	}

	/**
	 * Возвращаем текущий блог, ощущая влияния url.
	 * Если url содержит идентификатор блога’’ 
	 *   и он имеет родство с топиком - вернёться блог’’. 
	 *   в случає отсутствии родства - возвращаем дефолтный блог топика
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
