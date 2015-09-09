<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSAccordion
{
	protected $id		= null;
	protected $titles 	= array();
	protected $contents = array();

	public function __construct($id) {
		$this->id	   = preg_replace('#[^A-Z0-9_\. -]#i', '', $id);
	}

	public function addTitle($label, $id) {
		$this->titles[] = (object) array('label' => $label, 'id' => $id);
	}

	public function addContent($content) {
		$this->contents[] = $content;
	}

	public function render() {
		?>
		<div class="accordion" id="<?php echo $this->id; ?>">
			<?php foreach ($this->titles as $i => $title) { ?>
				<div class="accordion-group rsmg_<?php echo $title->id?>">
					<div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#<?php echo $this->id; ?>" href="#body-<?php echo $this->titles[$i]->id;?>"><?php echo JText::_($title->label); ?></a>
					</div>
					<div class="accordion-body collapse <?php if ($i == 0) { ?> in<?php } ?>" id="body-<?php echo $this->titles[$i]->id;?>">
						<div class="accordion-inner">
							<?php echo $this->contents[$i]; ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}