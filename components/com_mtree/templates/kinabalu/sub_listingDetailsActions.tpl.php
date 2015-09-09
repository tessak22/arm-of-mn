<?php
if( $this->show_actions_rating_fav ) {
	?>
	<div class="row-fluid">
	<div class="span12 actions-rating-fav">
	<?php if( $this->show_actions ) { ?>
		<div class="actions">
			<?php
			$this->plugin( 'ahrefreview', $this->link, array("class"=>"btn", "rel"=>"nofollow") );
			$this->plugin( 'ahrefrecommend', $this->link, array("class"=>"btn", "rel"=>"nofollow") );
			$this->plugin( 'ahrefprint', $this->link, array("class"=>"btn", "rel"=>"nofollow") );
			$this->plugin( 'ahrefcontact', $this->link, array("class"=>"btn", "rel"=>"nofollow") );
			$this->plugin( 'ahrefvisit', $this->link, '', 1, array("class"=>"btn", "rel"=>"nofollow") );
			$this->plugin( 'ahrefreport', $this->link, array("class"=>"btn", "rel"=>"nofollow") );
			$this->plugin( 'ahrefclaim', $this->link, array("class"=>"btn", "rel"=>"nofollow") );
			$this->plugin( 'ahrefownerlisting', $this->link, array("class"=>"btn") );
			$this->plugin( 'ahrefmap', $this->link, array("class"=>"btn", "rel"=>"nofollow") );
			?></div>
		</div>
		</div><?php
	}
}