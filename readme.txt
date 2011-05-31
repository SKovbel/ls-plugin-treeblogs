Плагин Treeblogs

Позволяет создавать дерево блогов.

Добавить после {if count($aTopics)>0} для отображения подблогов
<!-- PluginTreeblogs-->
{if $aBlogsSub}
    <div class="sortThemes">
        {foreach from=$aBlogsSub item=oBlogSub}
        <a class="blogs-filter {if $oBlogSub->getUrl()|in_array:$aBlogFilter}active{/if}"href="{router page='blog'}{$oBlogSub->getUrl()}">{$oBlogSub->getTitle()}</a>
        {/foreach}
    </div>
{/if}
<!-- /PluginTreeblogs-->


В ActionTopic/add.tpl	
Удалить 
	<p><label for="blog_id">{$aLang.topic_create_blog}</label>
	<select name="blog_id" id="blog_id" onChange="ajaxBlogInfo(this.value);">
	<option value="0">{$aLang.topic_create_blog_personal}</option>
	{foreach from=$aBlogsAllow item=oBlog}
	<option value="{$oBlog->getId()}" {if $_aRequest.blog_id==$oBlog->getId()}selected{/if}>{$oBlog->getTitle()}</option>
	{/foreach}     					
	</select></p>
     				
В topic_list.tpl, topic.tpl
Удалить	
<li><a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>&nbsp;&nbsp;</li>
 
Добавить внутри <ul class="action">
<!-- PluginTreeblogs-->
	{hook run='get_topics_blogs' oTopic=$oTopic}
<!-- /PluginTreeblogs-->
	
  	   			