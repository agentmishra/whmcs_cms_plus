<?php
add_hook('cms/client/view/vars', 1, 'cms_comments_add_vars');
function cms_comments_add_vars($vars){
	$conf = whmcs_cms_config();
	if($vars['content']['comments'] == 1 && $conf['comments'] != ''){
		if($conf['comments'] == 'facebook'){
			global $CONFIG;
			return array('comments' => '<div class="fb-comments" data-href="'.$CONFIG['SystemURL'].$_SERVER['REQUEST_URI'].'" data-width="100%" data-num-posts="'.$conf['facebook_comments_limit'].'" data-colorscheme="'.$conf['facebook_comments_style'].'"></div>
				<div id="fb-root"></div>
				<script>
					(function(d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id)) return;
						js = d.createElement(s); 
						js.id = id;
						js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
						fjs.parentNode.insertBefore(js, fjs);
					}(document, "script", "facebook-jssdk"));
				</script>'
			);
		}elseif($conf['comments'] == 'disqus'){
			return array('comments' => '<div id="disqus_thread"></div>
			    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
			    <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
			    <script type="text/javascript">
			        var disqus_shortname = "'.$conf['disqus_comments'].'";
			        (function() {
			            var dsq = document.createElement("script"); dsq.type = "text/javascript"; dsq.async = true;
			            dsq.src = "//" + disqus_shortname + ".disqus.com/embed.js";
			            (document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(dsq);
			        })();
			    </script>'
			);
		}
	}
	return array('comments' => '');
}