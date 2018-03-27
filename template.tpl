{if $css}<style type="text/css">{$css}</style>{/if}
{include file="$cmstpl"}
{literal}
<script>
$(document).ready(function(){
	$('#languagefrm').attr('action', window.location.href);
});
</script>
{/literal}