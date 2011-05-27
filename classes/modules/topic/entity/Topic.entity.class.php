<?php

class PluginTreeblogs_ModuleTopic_EntityTopic extends PluginTreeblogs_Inherit_ModuleTopic_EntityTopic
{
	private $aBlogs;
	/**
	 * Строим массив блогов для топика
	 * @param int blogid
	 * @return array[blogid]
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
     * Переопределяем url для мультиблога
     * @param int $blogId
     * @return array
     */
	/*
    public function getUrl() {
    	if (Router::GetAction()=="blog"){
	        if ($this->getBlog()->getType()!='personal') {
	        	//$blogUrl = Router::GetActionEvent();
	        	//$oBlog = $this->Blog_GetBlogByUrl($blogUrl);
	        	if ( in_array($this->getBlog()->getId(), $this->GetBlogs()) ){
	        		return Router::GetPath('blog').(Router::GetActionEvent()).'/'.$this->getId().'.html';
				} else {
	    			return parent::getUrl();
				} 
	        }
    	}
		return parent::getUrl();
    }*/
    
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
