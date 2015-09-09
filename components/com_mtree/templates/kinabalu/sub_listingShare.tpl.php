<div class="listing-share">
	<?php
	$uri = JUri::getInstance();

	$share_title = $this->link->link_name;
	$share_description = JFactory::getDocument()->getDescription();
	$share_url = $uri->toString(array( 'scheme', 'host', 'port' )) .
		JRoute::_( 'index.php?option=com_mtree&task=viewlink&link_id='.$this->link->link_id, false);

	$share_image = '';
	if (isset($this->images[0])) {
		$share_image = $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_original_image'] . $this->images[0]->filename;
	}
	?>

	<div class="row-fluid">
		<div class="span12">
			<?php if ($this->config->get('show_share_with_email')) { ?>
			<div class="listing-share-item listing-share-email"><a title="<?php echo JText::_( 'COM_MTREE_LISTING_SHARE_EMAIL_TITLE' ) ?>" href="<?php

				echo 'mailto:?';
				echo 'subject=';
				echo JText::sprintf( 'COM_MTREE_LISTING_SHARE_EMAIL_SUBJECT', $share_title );
				echo '&body=';
				echo rawurlencode(JText::sprintf(
					'COM_MTREE_LISTING_SHARE_EMAIL_BODY',
					$share_description,
					$share_url
				));
			?>"><i class="fa fa-envelope"></i></a></div>
			<?php }

			if ($this->config->get('show_share_with_facebook')) { ?>
			<div class="listing-share-item listing-share-facebook"><a target="_blank" href="<?php
				echo 'https://www.facebook.com/sharer/sharer.php?u=';
				echo $share_url;
			?>" onclick="void window.open(this.href, 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;"><i class="fa fa-facebook"></i></a></div>
			<?php }

			if ($this->config->get('show_share_with_linkedin')) { ?>
			<div class="listing-share-item listing-share-linkedin"><a href="<?php
				echo 'https://www.linkedin.com/shareArticle?mini=true&url=';
				echo rawurlencode($share_url);
				echo '&title=' . rawurlencode($share_title);
				echo '&summary=' . rawurlencode($share_description);
				?>" onclick="void window.open(this.href, 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;"><i class="fa fa-linkedin"></i></a></div>
			<?php }

			if ($this->config->get('show_share_with_twitter')) { ?>
			<div class="listing-share-item listing-share-twitter"><a href="<?php
				echo 'https://twitter.com/intent/tweet?text=';
				echo rawurlencode($share_title);
				echo '&url=' . rawurlencode($share_url);
				// echo '&via=' . twitter-handle;
			?>" onclick="void window.open(this.href, 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;"><i class="fa fa-twitter"></i></a></div>
			<?php }

			if ($this->config->get('show_share_with_pinterest')) { ?>
			<div class="listing-share-item listing-share-pinterest"><a href="<?php
				echo 'https://pinterest.com/pin/create/link/?url=';
				echo rawurlencode($share_url);
				echo '&description=' . $share_title;
				echo '&media=' . $share_image;
			?>" onclick="void window.open(this.href, 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;"><i class="fa fa-pinterest"></i></a></div>
			<?php }

			if ($this->config->get('show_share_with_googleplus')) { ?>
			<div class="listing-share-item listing-share-google-plus"><a href="<?php
				echo 'https://plus.google.com/share?url=';
				echo rawurlencode($share_url);
			?>" onclick="void window.open(this.href, 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;"><i class="fa fa-google-plus"></i></a></div>
			<?php }?>

		</div>
	</div>

</div>

<?php include $this->loadTemplate( 'sub_listingFacebookLike.tpl.php' ); ?>
