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
		$this->AddHook('template_get_topics_blogs', 'TemplateTopicShow', __CLASS__);

		$this->AddHook('topic_add_show', 'topicEditShow', __CLASS__);
		$this->AddHook('topic_edit_show', 'topicEditShow', __CLASS__);

		$this->AddHook('topic_add_after', 'TopicEditAf', __CLASS__);
		$this->AddHook('topic_edit_after', 'TopicEditAf', __CLASS__);

	}

	/**
	 * тємплайт хук. Цепляеться на форму редактирования/добавления топика.
	 * Генерит иерархию select box.
	 *
	 * @return string
	 */
	public function TemplateFormAddTopicBegin()
	{
		$topicId = $_REQUEST['topic_id'];
		if (isset($topicId)){ /*редактирование топика*/
			$oTopic		= $this->Topic_GetTopicsAdditionalData($topicId);
			$oTopic		= $oTopic[$topicId];
			$subblogs	= $this->Topic_GetTopicSubBlogs($oTopic->getId());
			array_unshift($subblogs, $oTopic->getBlogId());
				
			$groups = array();
			$i=0;
			foreach ($subblogs as $blog_id) {
					
				$aBlogsId = $this->Blog_BuildTreeBlogsFromTail($blog_id);
				$allBlogs = array();
				$selected = array();
				$j=0;
				foreach ($aBlogsId as $blogid){
					$blogs	= $this->Blog_GetBlogsTreeLevel($blogid);
					$allBlogs[$j]	= $this->Blog_GetBlogsAdditionalData($blogs);
					$j++;
				}
				$tailBlogs = $this->Blog_GetSubBlogs($aBlogsId[$j-1]); /*конечный єлемент*/
				if (count($tailBlogs) > 0) {
					$allBlogs[$j] = $this->Blog_GetBlogsAdditionalData($tailBlogs);
					$j++;
				}

				$groups[$i]['blog_id']			= $blog_id;
				$groups[$i]['Blogs']			= $allBlogs;
				$groups[$i]['ActiveBlogId']		= $aBlogsId;
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
	 * Хук цепляющийся на пост обработку редак/добав топика.
	 * Создаём связи между топиком и блогами.
	 *
	 * @param array $data
	 * @return string
	 */
	public function TopicEditAf($data){
		$oTopic = $data['oTopic'];
		$this->Topic_MergeTopicBlogs($oTopic->getId(), $oTopic->getBlogId());
	}

	/**
	 * Темплайт хук цепляеться на отображение топика (короткий вид и полный).
	 * Генерирует навигацию по веткам дерева для каждого блога топика.
	 *
	 * @return string
	 * @param array $data
	 */
	public function TemplateTopicShow($aData)
	{
		$oTopic = $aData['oTopic'];
		$oBlogsTopic = $this->Blog_GetTopicFullTree($oTopic);
		$this->Viewer_Assign('aBlogsTree', $oBlogsTopic);
	}

	 
	/**
	 * Системный хук редактирования/добавления топика.
	 * Подключаем необходимые css & js
	 *
	 * @return string
	 * @return $aData
	 */
	public function topicEditShow($aData) {
		$this->Viewer_AppendScript( Plugin::GetTemplatePath(__CLASS__) . 'js/blog-selector.js');
		$this->Viewer_AppendStyle( Plugin::GetTemplatePath(__CLASS__) . 'css/blog-selector.css');
	}

}