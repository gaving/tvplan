{include file="msgbox.tpl"}

{section name=i loop=$posts}
<div class="post">
	<div class="postdate" onmouseover="toggleX('{$posts[i].id}');" onmouseout="toggleX('{$posts[i].id}');">
		<span class="subHeading">
			<a href="?news,remove,{$posts[i].id}">
				<div id="{$posts[i].id}" style="float: left; color: #FFF;">X</div>
			</a>
			<dfn>{$posts[i].show}</dfn>
		</span>
	</div>
	<h3>"{$posts[i].title}"</h3>
	{$posts[i].body}
</div>
{/section}

<div class="bugFix">
	&nbsp;
</div>
