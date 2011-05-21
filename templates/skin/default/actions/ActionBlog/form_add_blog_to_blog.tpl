<p id="blog-toblog"
   <label for="parent_id">{$aLang.blog_assign}:</label>
    <select name="parent_id" id="parent_id">
        <option value="0" {if $parentId == 0}selected{/if}>{$aLang.no_assign}</option>
        {foreach from=$aBlogs item=oBlog name=el2}
        <option value="{$oBlog.id}" {if $parentId == $oBlog.id}selected{/if}>{$oBlog.title}</option>
        {/foreach}
    </select>
</p>
