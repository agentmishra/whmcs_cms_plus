{if $content.breadcrumbs}{$breadcrumbnav}{/if}

<h1 class="cms-plus-title" id="cms-plus-title-{$content.id}">{$content.title}</h1>

<article class="cms-plus-content" id="cms-plus-content-{$content.id}">
    {include file="string:$textcontent"}

	{if $content.fb}
		{literal}
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=627486317336388&version=v2.0";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
		<div class="fb-like" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div>
		{/literal}
	{/if}
	{if $content.twitter}
		{literal}
			<style type="text/css">
				#twitter-widget-0{
					margin-bottom: -4px;
					margin-left: 20px;
				}
			</style>
			<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		{/literal}
	{/if}
	{if $content.gplus}
		{literal}
			<style type="text/css">
				#___plusone_0{
					margin-bottom: -4px !important;
					vertical-align: top !important;
				}
			</style>
			<script src="https://apis.google.com/js/platform.js" async defer></script>
			<div class="g-plusone" data-size="medium"></div>
		{/literal}
	{/if}
</article>

<hr/>

<div class="pagination">
  <ul>
  	{if $prev_link}<li><a href="{$prev_link}" title="{$prev_link_text}"><< {$prev_link_text}</a></li>{/if}
	{if $next_link}<li><a href="{$next_link}" title="{$next_link_text}">{$next_link_text} >></a></li>{/if}
	</ul>
</div>

{$comments}