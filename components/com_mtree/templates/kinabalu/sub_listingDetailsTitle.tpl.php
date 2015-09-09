<h2 class="row-fluid"><?php
	$link_name = $this->fields->getFieldById(1);
	$this->plugin( 'ahreflisting', $this->link, $link_name->getOutput(1), '', array("edit"=>false,"delete"=>false,"link"=>false) );

	if (
		$this->my->id == $this->link->user_id
		&&
		(
			$this->config->get('user_allowmodify') == 1
			||
			$this->config->get('user_allowdelete') == 1
		)
		&&
		$this->my->id > 0
	) {
		?>
		<div class="btn-group pull-right"> <a class="btn dropdown-toggle" data-toggle="dropdown" href="#" role="button"> <span class="icon-cog"></span> <span class="caret"></span> </a>
			<ul class="dropdown-menu">
				<?php if( $this->config->get('user_allowmodify') == 1) { ?>
					<li class="edit-icon">
						<a href="<?php echo JRoute::_('index.php?option=com_mtree&task=editlisting&link_id='.$this->link->link_id); ?>">
							<span class="icon-edit"></span>
							<?php echo JText::_( 'COM_MTREE_EDIT' ); ?>
						</a>
					</li>
				<?php
				}

				if( $this->link->link_published && $this->link->link_approved && $this->config->get('user_allowdelete') == 1) { ?>
					<li class="delete-icon">
						<a href="<?php echo JRoute::_('index.php?option=com_mtree&task=deletelisting&link_id='.$this->link->link_id); ?>">
							<span class="icon-delete"></span>
							<?php echo JText::_( 'COM_MTREE_DELETE' ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php
	}
?></h2>
<?php

if ( !empty($this->mambotAfterDisplayTitle) )
{
	echo trim( implode( "\n", $this->mambotAfterDisplayTitle ) );
}
