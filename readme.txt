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


ActionTopic/add.tpl после <form action="" method="POST" enctype="multipart/form-data">
					{hook run='form_add_topic_topic_begin'}
	delete				
					<p><label for="blog_id">{$aLang.topic_create_blog}</label>
					<select name="blog_id" id="blog_id" onChange="ajaxBlogInfo(this.value);">
     					<option value="0">{$aLang.topic_create_blog_personal}</option>
     					{foreach from=$aBlogsAllow item=oBlog}
     						<option value="{$oBlog->getId()}" {if $_aRequest.blog_id==$oBlog->getId()}selected{/if}>{$oBlog->getTitle()}</option>
     					{/foreach}     					
     					</select></p>
     				
topic_list.tpl, topic.tpl после <ul class="action">					

				{foreach from=$aBlogsTree item=oTree name=tree}
				<ul class="treeblogs">
   				{foreach from=$oTree item=oBlog name=blogs}
					<li><a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>&nbsp;&nbsp;{if !$smarty.foreach.blogs.last}→{/if}</li>
   				{/foreach}
				</ul>
	   			{/foreach}
  			
  	   			