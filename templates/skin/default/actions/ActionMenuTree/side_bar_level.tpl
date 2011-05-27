{math equation="x+1" x=$level assign=level} 
{foreach from=$tree item=branch}
	<li class="level{$level-1}">
		{if $branch.child|@count > 0}
			<div class="{if in_array($branch.id,$aTreePath)}active{else}regular{/if}" id="d{$branch.id}" onclick="reverseMenu('{$branch.id}')"></div>
			<a class="{if $currTreePath ==$branch.id }active{else}regular{/if}" href="{$branch.url}">{$branch.title}</a>
			<ul class="{if in_array($branch.id,$aTreePath)}active{else}regular{/if} level{$level}" id="m{$branch.id}">
			{include file=$side_bar_level tree=$branch.child level=$level}
			</ul>
		{else}
			<div class="end"></div>
			<a  class="{if $currTreePath ==$branch.id }active{else}regular{/if}"  href="{$branch.url}">{$branch.title}</a>
		{/if}
	</li>
{/foreach}
