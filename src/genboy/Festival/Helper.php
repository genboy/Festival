<?php declare(strict_types = 1);
/** src/genboy/Festival2/Helper.php
 *
 * global helper
 *
 */
namespace genboy\Festival;

use pocketmine\math\Vector3;

class Helper {

    private $plugin;

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

    }

    public function isPluginLoaded(string $pluginName){

        return ($findplugin = $this->plugin->getServer()->getPluginManager()->getPlugin($pluginName)) !== null and $findplugin->isEnabled();

    }

}
