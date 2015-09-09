<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2014-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_5_10 extends mUpgrade
{
    function upgrade() {
        $database = JFactory::getDBO();

        // Fixed typo in Audio Player description
        $database->setQuery("UPDATE `#__mt_fieldtypes` SET `ft_desc` = 'Audio Player allows users to upload audio files and play the music from within the listing page. Provides basic playback options such as play, pause and volume control. Made possible by http://www.1pixelout.net/code/audio-player-wordpress-plugin/.' WHERE `field_type` = 'audioplayer'");
        $database->execute();

        updateVersion(3,5,10);
        $this->updated = true;
        return true;
    }
}
?>
