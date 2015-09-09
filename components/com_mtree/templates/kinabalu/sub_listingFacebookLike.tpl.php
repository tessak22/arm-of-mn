<?php
if ( $this->config->get('use_facebook_like') )
{
?>
<div class="listing-facebook-like">
	<div class="row-fluid">
		<div class="span12">
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) return;
					js = d.createElement(s); js.id = id;
					js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>
			<div class="fb-like" data-href="<?php echo $share_url; ?>" data-layout="standard" data-action="like" data-show-faces="true" data-share="false"></div>
		</div>
	</div>
</div>
<?php

}