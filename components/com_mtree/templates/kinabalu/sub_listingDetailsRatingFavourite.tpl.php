<?php
if( $this->config->get('show_favourite') == 1 || $this->config->get('show_rating') == 1 )
{
	echo '<div class="rating-fav">';
	if($this->config->get('show_rating')) {
		echo '<div class="rating">';
		$this->plugin( 'ratableRating', $this->link, $this->link->link_rating, $this->link->link_votes, $this->total_reviews);
		echo '<div id="total-votes">';
		if( $this->link->link_votes <= 1 ) {
			echo $this->link->link_votes . " " . strtolower(JText::_( 'COM_MTREE_VOTE' ));
		} elseif ($this->link->link_votes > 1 ) {
			echo $this->link->link_votes . " " . strtolower(JText::_( 'COM_MTREE_VOTES' ));
		}
		echo '</div>';
		echo '</div>';
	}

	if($this->config->get('show_favourite')) {
		?>
		<div class="favourite">
		<span class="fav-caption"><?php echo JText::_( 'COM_MTREE_FAVOURED' ) ?>:</span>
		<div id="fav-count"><?php echo number_format($this->total_favourites,0,'.',',') ?></div><?php
		if($this->my->id > 0){
			if($this->is_user_favourite) {
				?><div id="fav-msg"><a href="javascript:fav(<?php echo $this->link->link_id ?>,-1);"><?php echo JText::_( 'COM_MTREE_REMOVE_FAVOURITE' ) ?></a></div><?php
			} else {
				?><div id="fav-msg"><a href="javascript:fav(<?php echo $this->link->link_id ?>,1);"><?php echo JText::_( 'COM_MTREE_ADD_AS_FAVOURITE' ) ?></a></div><?php
			}
		} ?>
		</div><?php
	}
	echo '</div>';
}