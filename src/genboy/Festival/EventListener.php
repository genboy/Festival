<?php
/** Festival 1.0
 *
 *                          |~
 *                .___---^^^ ^^^---___.
 *         ___|~_/_____________________\___|~___
 *        |______\        _____        |________\
 *       |   |\   |      |  F  \      \    |\   \
 *  _________________________________________________
 *  .  ____   ___         _    |_|               _  .
 *  . |  __| | -_|  ___  | |_  | |   _  _  ___  | | .
 *  . |  _|  |___| |_ -| |  _| | |  | | | | .'| | | .
 *  . |_|          |___| |_|   |_|  \__/  |__,| |_| .
 *  _________________________________________________
 *                                        GENBOY 2018
 *
 * src/genboy/Festival/Main.php
 *
 * Options in config.yml
 * language: en/nl, Msgtype: msg/title/tip/pop, Areadisplay: off/op/on, Msgdisplay: off/op/on
 * Flags: god, pvp, flight, edit, touch, mobs, animals, effects, msg, passage, drop, tnt, fire, explode, shoot, hunger, perms, falldamage, cmdmode
 *
 */

declare(strict_types = 1);

namespace genboy\Festival;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\block\Block;

class EventListener implements Listener{

	private $plugin;

	public function __construct(Main $plugin){

		$this->plugin = $plugin;

	}

    // on..


}
?>
