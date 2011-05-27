<?php

/**
 * Плагин Treeblogs. Хуки для блогов
 */
class PluginTreeblogs_HookTopic extends Hook
{
	
    /**
     * Регистрируем нужные хуки
     *
     * @return void
     */
    public function RegisterHook()
    {
    	
    	$this->AddHook('template_form_add_topic_topic_begin', 'TemplateFormAddTopicBegin', __CLASS__);
        $this->AddHook('template_get_topics_blogs', 'TopicShowList', __CLASS__);
        
        $this->AddHook('topic_show', 'TopicShowList', __CLASS__);
        
        $this->AddHook('topic_add_show', 'topicEditShow', __CLASS__);
        $this->AddHook('topic_edit_show', 'topicEditShow', __CLASS__);
      
        $this->AddHook('topic_add_after', 'TopicEditAf', __CLASS__);
        $this->AddHook('topic_edit_after', 'TopicEditAf', __CLASS__);
        
    	$this->AddHook('init_action', 'InitAction', __CLASS__);

    }

    /**
     * редактирование / добавление топика, хук. Генерим деревья блогов
     *
     * @return string
     */
    public function TemplateFormAddTopicBegin()
    {
        $topicId = $_REQUEST['topic_id'];
        if (isset($topicId)){ /*редактирование топика*/
        	$oTopic 	= $this->Topic_GetTopicsAdditionalData($topicId);
        	$oTopic 	= $oTopic[$topicId];
			$subblogs   = $this->Topic_GetTopicSubBlogs($oTopic->getId());
			array_unshift($subblogs, $oTopic->getBlogId());
			
			$groups = array();
			$i=0;
        	foreach ($subblogs as $blog_id) {
			
	        	$aBlogsId = $this->Blog_BuildTreeBlogsFromTail($blog_id);
	        	$allBlogs = array();
	        	$selected = array();
	        	$j=0;
	        	foreach ($aBlogsId as $blogid){
	        		$blogs 			= $this->Blog_GetBlogsTreeLevel($blogid);
	        		$allBlogs[$j] 		= $this->Blog_GetBlogsAdditionalData($blogs);
	        		$j++;
	        	}
	        	$tailBlogs 				= $this->Blog_GetSubBlogs($aBlogsId[$j-1]); /*конечный єлемент*/
	        	if (count($tailBlogs) > 0) {
					$allBlogs[$j]        	= $this->Blog_GetBlogsAdditionalData($tailBlogs);
	        		$j++;
	        	}
	        	
	        	$groups[$i]['blog_id'] 			= $blog_id;
	        	$groups[$i]['Blogs'] 			= $allBlogs;
	        	$groups[$i]['ActiveBlogId'] 	= $aBlogsId;
				$i++;
        	}
			$this->Viewer_Assign('groups', $groups);
			return $this->Viewer_Fetch(Plugin::GetTemplatePath('treeblogs') . 'actions/ActionTopic/form_edit_topic.tpl');
        } else { /*добавлении топика*/
        	$aRootBlogs  	= $this->Blog_GetMenuBlogs(true);
        	$defRootBlogId 	= $aRootBlogs[0];
        	$aSubRootBlogs  = $this->Blog_GetSubBlogs($defRootBlogId);
        	$oaRootBlogs 	= $this->Blog_GetBlogsAdditionalData($aRootBlogs);
        	$oaSubRootBlogs = $this->Blog_GetBlogsAdditionalData($aSubRootBlogs);
        	$this->Viewer_Assign('blog_id', $defRootBlogId);
        	$this->Viewer_Assign('a0', $oaRootBlogs);
        	$this->Viewer_Assign('a1', $oaSubRootBlogs);
        	$groups=array();
	        $groups[0]['blog_id'] 			= $defRootBlogId;
	        $groups[0]['Blogs'] 			= array($oaRootBlogs, $oaSubRootBlogs);
	        $groups[0]['ActiveBlogId'] 		= array($defRootBlogId);
			$this->Viewer_Assign('groups', $groups);
			return $this->Viewer_Fetch(Plugin::GetTemplatePath('treeblogs') . 'actions/ActionTopic/form_edit_topic.tpl');
        	
        }
    }

    /**
     * Мержим блоги в топики
     *
     * @return string
     */    
    public function TopicEditAf($data){
		$oTopic = $data['oTopic'];
		$this->Topic_MergeTopicBlogs($oTopic->getId(), $oTopic->getBlogId());
		//$this->Topic_MergeTopicBlogs($oTopic->getId());    	
    }
    
    /**
     * Генерирует дерево для отобюражения в топике и топик листе
     *
     * @return string
     */    
    public function TopicShowList($aData)
    {
    	$oTopic = $aData['oTopic'];
		$oBlogsTopic = $this->Blog_GetTopicFullTree($oTopic);
		$this->Viewer_Assign('aBlogsTree', $oBlogsTopic);
    }

    	
    /**
     * Для редактирования и добавления подключаем css & js
     *
     * @return $aData
     */    
    public function topicEditShow($aData) {
        $this->Viewer_AppendScript(
                Plugin::GetTemplatePath(__CLASS__) . 'js/blog-selector.js');      
        $this->Viewer_AppendStyle(
                Plugin::GetTemplatePath(__CLASS__) . 'css/blog-selector.css');
                
     }
     
	public function InitAction(){
	}     

}