{$breadcrumbnav}

<h1 class="cms-plus-type-title" id="cms-plus-type-title-{$type.id}">{$type.name}</h1>

<article class="cms-plus-archive-description">
	{$type.description}
</article>

{foreach from=$articles item=article}
	<article class="cms-plus-content" id="cms-plus-content-{$article.id}">
		<h2><a href="{$type.slug}/{$article.slug}" title="{$article.title}">{$article.title}</a></h2>
		<div class="content">
			{assign var=articletext value=$article.content|html_entity_decode|truncate:270:"..."}
            {include file="string:$articletext"}
		</div>
		<p><a href="{$type.slug}/{$article.slug}">{$_lang.view} {$type.singular_name}</a></p>
		<hr/>
	</article>
{foreachelse}
	<p>{$_lang.no} {$type.singular_name}'s {$_lang.found}</p>
{/foreach}

{if !empty($articles)}
<div class="pagination">
  <ul>
  		<li {if !$prev_page} class="disabled"{/if}><a href="{$type.slug}?page={$prev_page}"{if !$prev_page} onclick="return false;"{/if}>&laquo;</a></li>
		{foreach from=$pages item=page}
			<li{if $current_page == $page} class="active"{/if}><a href="{$type.slug}?page={$page}">{$page}</a></li>
		{/foreach}
		<li {if !$next_page} class="disabled"{/if}><a href="{$type.slug}?page={$next_page}"{if !$next_page} onclick="return false;"{/if}>&raquo;</a></li>
	</ul>
</div>
{/if}