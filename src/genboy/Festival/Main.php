<?php
/** Festival 1.1.1
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

use genboy\Festival\lang\Language;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\Item;
use pocketmine\block\Block;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\FallingSand;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerBucketEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener{

	/** @var array[] */
	private $levels        = []; // list of level flags
	/** @var Area[] */
	public $areas          = []; // list of area objects
	/** @var array[] */
	public $flagset        = []; // list of area flags
	/** @var array[] */
	public $options        = []; // config options

	/** @var bool */
	private $god           = false;
	/** @var bool */
	private $pvp           = false;
	/** @var bool */
	private $flight        = false;
	/** @var bool */
	private $edit          = false;
	/** @var bool */
	private $touch         = false;
	/** @var bool */
	private $mobs          = false;
	/** @var bool */
	private $animals       = false;
	/** @var bool */
	private $effects       = false;
	/** @var bool */
	private $msg           = false;
	/** @var bool */
	private $passage       = false;
	/** @var bool */
	private $drop          = false;
	/** @var bool */
	private $explode       = false;
	/** @var bool */
	private $tnt           = false;
	/** @var bool */
	private $fire          = false;
	/** @var bool */
	private $shoot         = false;
	/** @var bool */
	private $hunger        = false;
	/** @var bool */
	private $perms         = false;
	/** @var bool */
	private $falldamage    = false;
	/** @var bool */
	private $cmdmode       = false;

	/** @var bool[] */
	private $selectingFirst    = [];
	/** @var bool[] */
	private $selectingSecond   = [];
	/** @var Vector3[] */
	private $firstPosition     = [];
	/** @var Vector3[] */
	private $secondPosition    = [];

	/** @var array[]
     * list of playernames with areanames they're in
     */
	private $inArea    = [];
	/** @var array[]
     * list of areanames with the full area objects (recreated in saveAreas function)
     */
	private $areaList  = [];
	/** @var array[]
     * list of areanames with active floatingtextparticle objects
     */
	private $areaTitles  = [];
	/** @var array[]
     * list of playernames in a global delay counter per player (skipptime)
     */
	private $skipsec   = [];
	/** @var array[]
     * list of playernames who have fall damage/teleport protection (skipptime)
     */
	public $playerTP   = [];

	/** Enable
	 * @return $this
	 */
	public function onEnable() : void{

        $this->getServer()->getPluginManager()->registerEvents($this, $this); // Load data & configurations
        $newchange = []; // list of missing config flags/options
		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		if(!file_exists($this->getDataFolder() . "areas.json")){
			file_put_contents($this->getDataFolder() . "areas.json", "[]");
		}
		if(!file_exists($this->getDataFolder() . "config.yml")){
			$c = $this->getResource("config.yml");
			$o = stream_get_contents($c);
			fclose($c);
			file_put_contents($this->getDataFolder() . "config.yml", str_replace("DEFAULT", $this->getServer()->getDefaultLevel()->getName(), $o));
            $newchange['Config'] = 'Festival setup..';
		}

		/** load default language translation class */
        $this->loadLanguage();

		$c = yaml_parse_file($this->getDataFolder() . "config.yml");
		
		// innitialize configurations & update options
		if( isset( $c["Options"] ) && is_array( $c["Options"] ) ){
            if(!isset($c["Options"]["Language"])){
				$c["Options"]["Language"] = 'en';
				$newchange['Msgtype'] = "! Language ".Language::translate("option-missing-in-config")." 'en'; ". Language::translate("option-see-configfile");
            }else if(isset($c["Options"]["Language"])){
                $this->loadLanguage($c["Options"]["Language"]);
			}
			if(!isset($c["Options"]["Msgtype"])){
				$c["Options"]["Msgtype"] = 'pop';
				$newchange['Msgtype'] = "! Msgtype ".Language::translate("option-missing-in-config")." 'pop'; ". Language::translate("option-see-configfile");
			}
			if(!isset($c["Options"]["Msgdisplay"])){
				$c["Options"]["Msgdisplay"] = 'off';
				$newchange['Msgtype'] = "! Msgdisplay ".Language::translate("option-missing-in-config")." 'off'; ". Language::translate("option-see-configfile");
			}
			if(!isset($c["Options"]["Areadisplay"])){
				$c["Options"]["Areadisplay"] = 'off';
				$newchange['Areadisplay'] = "! Areadisplay ".Language::translate("option-missing-in-config")." 'off'; ". Language::translate("option-see-configfile");
			}
            if(!isset($c["Options"]["AutoWhitelist"])){ // check since v1.0.5-12
				$c["Options"]["AutoWhitelist"] = 'on';
				$newchange['Msgtype'] = "! AutoWhitelist ".Language::translate("option-missing-in-config")." 'on'; ". Language::translate("option-see-configfile");
			}
			$this->options = $c["Options"];
		}else{
			$this->options = array("Language"=>"en", "Msgtype"=>"pop", "Msgdisplay"=>"off", "AutoWhitelist"=>"on"); // Fallback defaults
            $newchange['Options'] = "! ".Language::translate("option-missing-in-config")."; ". Language::translate("option-see-configfile");
		}

        // set defaults
		if(!isset($c["Default"]["God"])) {
			$c["Default"]["God"] = false;
		}
		if(!isset($c["Default"]["Edit"])) {
			$c["Default"]["Edit"] = true;
		}
		if(!isset($c["Default"]["Touch"])) {
			$c["Default"]["Touch"] = false;
		}
		if(!isset($c["Default"]["Msg"])) { // new in v1.0.3
			$c["Default"]["Msg"] = false;
		}
		if( isset($c["Default"]["Barrier"]) ){ // new in v1.0.4-11
			$c["Default"]["Passage"] =  $c["Default"]["Barrier"];
		}else if(!isset($c["Default"]["Passage"])) { // replaced in v1.0.5-11
			$c["Default"]["Passage"] = false;
		}
		if(!isset($c["Default"]["Perms"])) { // new in v1.0.4-11
			$c["Default"]["Perms"] = false;
		}
		if(!isset($c["Default"]["Drop"])) { // new in v1.0.4-11
			$c["Default"]["Drop"] = false;
		}
		if(!isset($c["Default"]["Animals"])) { // new in v1.0.7.5-dev
			$c["Default"]["Animals"] = false;
		}
		if(!isset($c["Default"]["Mobs"])) { // new in v1.0.7.5-dev
			$c["Default"]["Mobs"] = false;
		}
		if(!isset($c["Default"]["Effects"])) { // new in v1.0.5-12
			$c["Default"]["Effects"] = false;
		}
		if(!isset($c["Default"]["PVP"])) { // new in v1.0.6-13
			$c["Default"]["PVP"] = false;
		}
		if(!isset($c["Default"]["Flight"])) { // new in v1.0.6-13
			$c["Default"]["Flight"] = false;
		}
		if(!isset($c["Default"]["Explode"])) { // new in v1.0.7.9
			$c["Default"]["Explode"] = false;
            if( isset($c["Default"]["TNT"]) ){
                $c["Default"]["Explode"] = true;
            }
		}
		if(!isset($c["Default"]["TNT"])) { // new in v1.0.7.3
			$c["Default"]["TNT"] = false;
		}
		if(!isset($c["Default"]["Fire"])) { // new in v1.0.7.9
			$c["Default"]["Fire"] = false;
            if( isset($c["Default"]["TNT"]) ){
                $c["Default"]["Fire"] = true;
            }
		}
		if(!isset($c["Default"]["Shoot"])) { // new in v1.0.7
			$c["Default"]["Shoot"] = false;
		}
		if(!isset($c["Default"]["Hunger"])) { // new in v1.0.7
			$c["Default"]["Hunger"] = false;
		}
		if(!isset($c["Default"]["FallDamage"])) {
			$c["Default"]["FallDamage"] = false; // new in v1.0.7.3
		}
		if(!isset($c["Default"]["CMDmode"])) {
			$c["Default"]["CMDmode"] = false; // new in v1.0.7.8
		}

        // world default flag settings
		$this->god            = $c["Default"]["God"]; // original
		$this->edit           = $c["Default"]["Edit"]; // original
		$this->touch          = $c["Default"]["Touch"]; // original
		$this->msg            = $c["Default"]["Msg"]; // new in v1.0.2
		$this->passage        = $c["Default"]["Passage"]; // changed in v1.0.3-11
		$this->perms          = $c["Default"]["Perms"]; // new in v1.0.4-11
		$this->drop           = $c["Default"]["Drop"]; // new in v1.0.4-11
		$this->animals        = $c["Default"]["Animals"]; // new in v1.0.7.5-dev
		$this->mobs           = $c["Default"]["Mobs"]; // new in v1.0.7.5-dev
		$this->effects        = $c["Default"]["Effects"]; // new in v1.0.5-12
		$this->pvp            = $c["Default"]["PVP"]; // new in v1.0.6-13
		$this->flight         = $c["Default"]["Flight"]; // new in v1.0.6-13
		$this->explode        = $c["Default"]["Explode"]; // new in v1.0.7.9
		$this->tnt            = $c["Default"]["TNT"]; // new in v1.0.7.3
		$this->fire           = $c["Default"]["Fire"]; // new in v1.0.7.9
		$this->hunger         = $c["Default"]["Hunger"]; // new in v1.0.7.3
		$this->falldamage     = $c["Default"]["FallDamage"]; // new in  1.0.7.2-dev(1.0.8)
		$this->shoot          = $c["Default"]["Shoot"]; // new in  1.0.7.2-dev(1.0.8)
		$this->cmdmode        = $c["Default"]["CMDmode"]; // new in  1.0.7.8-dev(1.0.8)

        $this->flagset        = $c['Default']; // all flags :) new in v1.0.5-12
        
        // specified world default flag settings
		if(is_array( $c["Worlds"] )){
			foreach($c["Worlds"] as $level => $flags){
				if( isset($flags["Barrier"]) ){ // check since v1.0.3-11
					$flags["Passage"] = $flags["Barrier"]; // replaced in v1.0.5-11
					unset($flags["Barrier"]);
				}
				if( !isset($flags["Passage"]) ){ // changed in v1.0.3-11
					$flags["Passage"] = $this->passage;
				}
				if( !isset($flags["Perms"]) ){ // new v1.0.4-11
					$flags["Perms"] = $this->perms;
				}
				if( !isset($flags["Drop"]) ){ // new v1.0.4-11
					$flags["Drop"] = $this->drop;
				}
				if( !isset($flags["Animals"]) ){ // new v1.0.7.5-dev
					$flags["Animals"] = $this->animals;
				}
				if( !isset($flags["Mobs"]) ){ // new v1.0.7.5-dev
					$flags["Mobs"] = $this->effects;
				}
				if( !isset($flags["Effects"]) ){ // new v1.0.5-12
					$flags["Effects"] = $this->effects;
				}
				if( !isset($flags["PVP"]) ){ // new v1.0.6-13
					$flags["PVP"] = $this->pvp;
				}
				if( !isset($flags["Flight"]) ){ // new v1.0.6-13
					$flags["Flight"] = $this->flight;
				}
				if( !isset($flags["Explode"]) ){ // new v1.0.7.9
					$flags["Explode"] = $this->explode;
				}
				if( !isset($flags["TNT"]) ){ // new v1.0.7.3
					$flags["TNT"] = $this->tnt;
				}
				if( !isset($flags["Fire"]) ){ // new v1.0.7.9
					$flags["Fire"] = $this->fire;
				}
				if( !isset($flags["Hunger"]) ){ // new v1.0.7
					$flags["Hunger"] = $this->hunger;
				}
				if( !isset($flags["FallDamage"]) ){ // new in v1.0.7.2
					$flags["FallDamage"] = $this->falldamage;
				}
				if( !isset($flags["Shoot"]) ){ // new v1.0.7.2
					$flags["Shoot"] = $this->shoot;
				}
				if( !isset($flags["CMD"]) ){ // new in v1.0.7.2
					$flags["CMDmode"] = $this->cmdmode;
				}
				$this->levels[$level] = $flags;
			}
		}

        // innitialize default flags & update data
		$data = json_decode(file_get_contents($this->getDataFolder() . "areas.json"), true);

		if( isset( $data ) && is_array( $data ) ){
            foreach($data as $datum){
                $flags = $datum["flags"];
                if( isset($datum["flags"]["barrier"]) ){
                    $flags["passage"] = $datum["flags"]["barrier"]; // replaced in v1.0.5-11 can use both
                    unset($flags["barrier"]);
                    $newchange['Passage'] = "! " . Language::translate("barrier-is-passage-flag");
                }
                if( !isset($datum["flags"]["perms"]) ){ // new flags v 1.0.5-12
                    $flags["perms"] = false;
				    $newchange['Perms'] = "! Perms ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["drop"]) ){ // new flags v 1.0.5-12
                    $flags["drop"] = false;
				    $newchange['canInteract'] = "! Drop ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["animals"]) ){ // new flags v 1.0.7.5-dev
                    $flags["animals"] = false;
				    $newchange['Animals'] = "! Animals ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["mobs"]) ){ // new flags v 1.0.7.5-dev
                    $flags["mobs"] = false;
				    $newchange['Mobs'] = "! Mobs ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["effects"]) ){ // new flags v 1.0.5-12
                    $flags["effects"] = false;
				    $newchange['Effects'] = "! Effects ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["pvp"]) ){ //new flags v 1.0.6-13
                    $flags["pvp"] = false;
				    $newchange['PVP'] = "! PVP ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["flight"]) ){ //new flags v 1.0.6-13
                    $flags["flight"] = false;
				    $newchange['Flight'] = "! Flight ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["explode"]) ){ // new flags v 1.0.7.9
                    $flags["explode"] = false;
				    $newchange['Explode'] = "! Explode ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["tnt"]) ){ // new flags v 1.0.7.3
                    $flags["tnt"] = false;
				    $newchange['TNT'] = "! TNT ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["fire"]) ){ // new flags v 1.0.7.9
                    $flags["fire"] = false;
				    $newchange['Fire'] = "! Fire ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["hunger"]) ){ // new flags v 1.0.7
                    $flags["hunger"] = false;
				    $newchange['Hunger'] = "! Hunger ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( isset($datum["flags"]["nofalldamage"]) ){
                    $flags["falldamage"] = $datum["flags"]["nofalldamage"]; // replaced in v1.0.5-11 can use both
                    unset($flags["nofalldamage"]);
                    $newchange['FallDamage'] = "! " . Language::translate("no-is-falldamage-flag");
                }
                if( !isset($datum["flags"]["falldamage"]) ){ //new in v1.0.7.3
                    $flags["falldamage"] = false;
				    $newchange['FallDamage'] = "! FallDamage ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["shoot"]) ){ //new in v1.0.7.4
                    $flags["shoot"] = false;
				    $newchange['Shoot'] = "! Shoot ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                if( !isset($datum["flags"]["cmdmode"]) ){ //new in v1.0.7.4
                    $flags["cmdmode"] = false;
				    $newchange['CMDmode'] = "! Event Command mode ".Language::translate("flag-missing-in-config")." 'false'; ". Language::translate("option-see-configfile");
                }
                // setup area's to use
                new Area($datum["name"], $datum["desc"], $flags, new Vector3($datum["pos1"]["0"], $datum["pos1"]["1"], $datum["pos1"]["2"]), new Vector3($datum["pos2"]["0"], $datum["pos2"]["1"], $datum["pos2"]["2"]), $datum["level"], $datum["whitelist"], $datum["commands"], $datum["events"], $this);
            }
        }
		$this->saveAreas(); // all save $this->areaList available :)

		/** load language translation class */
        $this->loadLanguage( $languageCode = $this->options["Language"] );

        /** console output */
        $this->getLogger()->info( Language::translate("enabled-console-msg") );

        $this->codeSigned(); // codesign

		$ca = 0; // plugin area command count
		$fa = 0; // plugin area flag count
		foreach( $this->areas as $a ){
            foreach($a->flags as $flag){
                if($flag){
                    $fa++;
                }
            }
			$ca = $ca + count( $a->getCommands() );
		}
        $this->getLogger()->info( $fa.' '.Language::translate("flags").' '.Language::translate("select-and").' '. $ca .' '. Language::translate("cmds") .' '.Language::translate("select-in").' '. count($this->areas)  .' '.  Language::translate("areas"));

		// warnings changes
		if( count($newchange) > 0 ){
            foreach($newchange as $ttl => $txt){
			     $this->getLogger()->info( $ttl . ": " . $txt );
            }
		}
	}

    /** load language ( v1.0.7.7-dev )
	 * @var plugin config[]
     * @file resources en.json
     * @file resources nl.json
	 * @var obj Language
	 */
    public function loadLanguage($languageCode =  'en' ){
      $resources = $this->getResources(); // read files in resources folder
      foreach($resources as $resource){
        if($resource->getFilename() === "en.json"){
          $default = json_decode(file_get_contents($resource->getPathname(), true), true);
        }
        if($resource->getFilename() === $languageCode.".json"){
          $setting = json_decode(file_get_contents($resource->getPathname(), true), true);
        }
      }
      if(isset($setting)){
        $langJson = $setting;
      }else{
        $langJson = $default;
      }
      new Language($this, $langJson);
    }

    /** set language
	 * @var str lang
	 * @var obj Player
	 */
    public function setLanguage( $lang, $player ){
        $this->options["Language"] = $lang;
        $this->loadLanguage();
        $msg = TextFormat::AQUA . Language::translate("language-selected");
        $this->areaMessage( $msg, $player );
    }

    /** Flag check (synonym to original name)
	 * @param string $flag
	 * @return str $flag
     */
    public function isFlag( $str ){
        // flag names
        $names = [
            "god","save",
            "pvp",
            "flight", "fly",
            "edit","build","break","place",
            "touch","interact",
            "mobs","mob",
            "animals","animal",
            "effects","magic","effect",
            "tnt",
            "explode","explosion","explosions",
            "fire","fires","burn",
            "hunger","starve",
            "drop",
            "msg","message",
            "passage","pass","barrier",
            "perms","perm",
			"falldamage","nofalldamage","fd","nfd","fall",
            "shoot", "launch",
            "cmdmode","commandmode","cmdm",
        ];
        $str = strtolower( $str );
        $flag = false;
        if( in_array( $str, $names ) ) {
            $flag = $str;
            if( $str == "save" ){
                $flag = "god";
            }
            if( $str == "fly" ){
                $flag = "flight";
            }
            if( $str == "build" || $str == "break" || $str == "place" ){
                $flag = "edit";
            }
            if( $str == "touch" || $str == "interact" ){
                $flag = "touch";
            }
            if( $str == "animals" || $str == "animal" ){
                $flag = "animals";
            }
            if( $str == "mob" || $str == "mobs" ){
                $flag = "mobs";
            }
            if( $str == "magic" || $str == "effect" ){
                $flag = "effects";
            }
            if( $str == "message" ){
                $flag = "msg";
            }
            if( $str == "perm" ){
                $flag = "perms";
            }
            if( $str == "pass" || $str == "barrier" ){
                $flag = "passage";
            }
            if( $str == "explosion" || $str == "explosions" || $str == "explode" ){
                $flag = "explode";
            }
            if( $str == "tnt"  ){
                $flag = "tnt";
            }
            if( $str == "fire" || $str == "fires" || $str == "burn" ){
                $flag = "fire";
            }
            if( $str == "shoot" || $str == "launch" ){
                $flag = "shoot";
            }
            if( $str == "effect" || $str == "effects" ){
                $flag = "effects";
            }
            if( $str == "hunger" || $str == "starve" ){
                $flag = "hunger";
            }
			if( $str == "nofalldamage" || $str == "falldamage" || $str == "fd" || $str == "nfd" || $str == "fall"){
				$flag = "falldamage";
			}
            if( $str == "cmdmode" || $str == "commandmode" || $str == "cmdm"){ // ! command is used as function..
                $flag = "cmdmode";
            }
        }
        return $flag;
    }


    /** COMMANDS
	 * @param CommandSender $sender
	 * @param Command $cmd
	 * @param string $label 
	 * @param array $args
	 * @return bool 
	 */
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		if(!($sender instanceof Player)){
            $sender->sendMessage( TextFormat::RED . Language::translate("cmd-ingameonly-msg") ); //$sender->sendMessage(TextFormat::RED . "Command must be used in-game.");
			return true;
		}
		if(!isset($args[0])){
			return false;
		}
		$playerName = strtolower($sender->getName());
		$action = strtolower($args[0]);
		$o = "";
		switch($action){
            case "lang": // experiment v1.0.7.7-dev
                if( isset($args[1]) ){
                    if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") ||  $sender->hasPermission("festival.command.fe.lang")){
                        $lang = $args[1];
                        $this->setLanguage( $lang, $sender );
                    }
                }
            break;
            case "titles":
				if( $sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->isOp() || $this->isWhitelisted($sender) ){
                    if( $this->options["Areadisplay"] == 'op' ||  $this->options["Areadisplay"] == 'on' ){
                        if( isset($this->areaTitles[strtolower($sender->getName())]) && count($this->areaTitles[strtolower($sender->getName())]) > 0 ){
                            foreach($this->areas as $area){
                                $this->hideAreaTitle( $sender, $sender->getPosition()->getLevel(), $area );
                            }
                            $this->areaTitles[strtolower($sender->getName())] = [];
                            $o = TextFormat::RED .  "Area floating titles off!";
                        }else{
                            $this->checkAreaTitles(  $sender, $sender->getPosition()->getLevel() );
                            $o = TextFormat::GREEN .  "Area floating titles on!";
                        }
                    }else{
                        $o = TextFormat::YELLOW .  "Area floating titles not available";
                    }
				}else{
                    $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand"); //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
                }
			break;
			case "pos1":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") ||  $sender->hasPermission("festival.command.fe.pos1")){
					if(isset($this->selectingFirst[$playerName]) || isset($this->selectingSecond[$playerName])){
                        $o = TextFormat::RED . Language::translate("pos-select-active"); //$o = TextFormat::RED . "You're already selecting a position!";
					}else{
						$this->selectingFirst[$playerName] = true;
                        $o = TextFormat::GREEN . Language::translate("make-pos1"); //$o = TextFormat::GREEN . "Please place or break the first position.";
					}
				}else{
                    $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand"); //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
			break;
			case "pos2":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") ||  $sender->hasPermission("festival.command.fe.pos2")){
					if(isset($this->selectingFirst[$playerName]) || isset($this->selectingSecond[$playerName])){
                        $o = TextFormat::RED . Language::translate("pos-select-active"); //$o = TextFormat::RED . "You're already selecting a position!";
					}else{
						$this->selectingSecond[$playerName] = true;
						$o = TextFormat::GREEN . Language::translate("make-pos2"); //$o = TextFormat::GREEN . "Please place or break the second position.";
					}
				}else{
                    $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand"); //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
			break;
			case "create":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.area") || $sender->hasPermission("festival.command.fe.create")){
					if(isset($args[1])){
						if(isset($this->firstPosition[$playerName], $this->secondPosition[$playerName])){
							if( $args[1] != '' && $args[1] != ' ' ){
                                if( !isset($this->areas[strtolower($args[1])]) ){
                                    // get level default flags
                                    $flags = $this->flagset;
                                    if( isset($this->levels[$sender->getLevel()->getName()]) ){
                                        if( is_array( $this->levels[$sender->getLevel()->getName()] ) ){
                                            $flags = $this->levels[$sender->getLevel()->getName()];
                                        }
                                    }
                                    $whitelist = []; // get default whitelisting
                                    if( $this->options["AutoWhitelist"] == "on" ){
                                        $whitelist = [$playerName];
                                    }
                                    new Area(
                                        strtolower($args[1]),
                                        "",
                                        [   "edit" => $flags['Edit'],
                                            "god" => $flags['God'],
                                            "pvp" => $flags["PVP"],
                                            "flight"=> $flags["Flight"],
                                            "touch" => $flags['Touch'],
                                            "animals" => $flags['Animals'],
                                            "mobs" => $flags['Mobs'],
                                            "effects" => $flags['Effects'],
                                            "msg" => $flags['Msg'],
                                            "passage" => $flags['Passage'],
                                            "drop" => $flags['Drop'],
                                            "explode" => $flags['Explode'],
                                            "tnt" => $flags['TNT'],
                                            "fire" => $flags['Fire'],
                                            "shoot" => $flags['Shoot'],
                                            "hunger" => $flags['Hunger'],
                                            "perms" => $flags['Perms'],
                                            "falldamage" => $flags['FallDamage'],
                                            "cmdmode" => $flags['CMDmode']
                                        ],
                                        $this->firstPosition[$playerName],
                                        $this->secondPosition[$playerName],
                                        $sender->getLevel()->getName(),
                                        $whitelist,
                                        [],
                                        [],
                                        $this
                                    );

                                    $this->saveAreas();
                                    unset($this->firstPosition[$playerName], $this->secondPosition[$playerName]);
                                    $o = TextFormat::AQUA . Language::translate("area-created"); //$o = TextFormat::AQUA . "Area created!";
                                }else{
                                    $o = TextFormat::RED . Language::translate("area-name-excist"); //$o = TextFormat::RED . "An area with that name already exists.";
                                }
                            }else{
                                $o = TextFormat::RED . Language::translate("give-area-name") . ' (/fe create <name>)' ; //$o = TextFormat::RED . "Enter a name for the area (/fe create <name>).";
                            }
                        }else{
                            $o = TextFormat::RED . Language::translate("select-both-pos-first"); //$o = TextFormat::RED . "Please select both positions first.";
                        }
					}else{
                        $o = TextFormat::RED . Language::translate("give-area-name") . ' (/fe create <name>)'; //$o = TextFormat::RED . "Please specify a name for this area (/fe create <name>).";
					}
				}else{
                    $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand"); //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
            break;
			case "desc":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") ||  $sender->hasPermission("festival.command.fe.desc")){
					if(isset($args[1])){
						if(isset($this->areas[strtolower($args[1])])){
							if(isset($args[2])){

				                $ar = $args[1];
								unset($args[0]);
								unset($args[1]);
								$desc = implode(" ", $args);
								$area = $this->areas[strtolower($ar)];
								$area->desc = $desc;
								$this->saveAreas();
								$o = TextFormat::GREEN . Language::translate("area") . ' ' . TextFormat::LIGHT_PURPLE . $area->getName() . ' ' . TextFormat::GREEN . Language::translate("desc-saved");

							}else{
                                $o = TextFormat::RED . Language::translate("desc-write-usage"); // Please write the description. Usage /fe desc <areaname> <..>
							}
						}else{
                            $o = TextFormat::RED . Language::translate("area-not-excist"); // Area does not excist
						}
					}else{
                        $o = TextFormat::RED . Language::translate("desc-specify-area"); // Please specify an area to edit the description. Usage: /fe desc <areaname> <desc>
					}
				}else{  
                    $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand"); // You do not have permission to use this subcommand
				}
            break;
			case "list":
				if( $sender->hasPermission("festival") || $sender->hasPermission("festival.command") ||  $sender->hasPermission("festival.command.fe.list")){
                    $levelNamesArray = scandir($this->getServer()->getDataPath() . "worlds/");
                    foreach($levelNamesArray as $levelName) {
                        if($levelName === "." || $levelName === "..") {
                        continue;
                        }
                        $this->getServer()->loadLevel($levelName); //Note that this will return false if the world folder is not a valid level, and could not be loaded.
                    }
                    $lvls = $this->getServer()->getLevels();
                    $o = '';
                    $l = '';

                    if( isset( $args[1] )){
                        $l = $args[1];
                    }else{
                        $l = false;
                    }

                    foreach( $lvls as $lvl ){
                        $i = 0;
                        $t = '';
                        foreach($this->areas as $area){
                            if( $area->getLevelName() == $lvl->getName() ){
                                if( ( !empty($l) && $l == $lvl->getName() ) || $l == false ){
                                    $t .= $this->areaInfoDisplayList( $area );
                                    $i++;
                                }
                            }
                        }
                        if( $i > 0 ){
                            $o .= TextFormat::DARK_PURPLE ."---- ".Language::translate("area-list")." ----\n";
                            $o .= TextFormat::GRAY . Language::translate("level") .' ' . TextFormat::YELLOW . $lvl->getName() .":\n". $t;
                        }
                    }
                    if($o != ''){
                        $o .= TextFormat::DARK_PURPLE ."----------------\n";
                    }
                    if($o == ''){
                        //$o = "There are no areas that you can edit";
                        $o = TextFormat::GRAY . Language::translate("area-no-area-to-edit");
                    }
                }
            break;
			case "here":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") ||  $sender->hasPermission("festival.command.fe.here")){
					$o = "";
                    $playername = strtolower($sender->getName());
                    foreach($this->inArea[$playername] as $areaname){
                        if( isset($this->areaList[ $areaname ]) ){
                            $area = $this->areaList[$areaname];
                            $o .= TextFormat::DARK_PURPLE ."---- ".Language::translate("area-here")." ----\n";
                            $o .= $this->areaInfoDisplayList( $area );
							$o .= TextFormat::DARK_PURPLE ."----------------\n";
                        }
                    }
					if($o === "") {
						//$o = TextFormat::RED . "You are in an unknown area";
                        $o = TextFormat::RED . Language::translate("in-unknown-area");
					}
				}
            break;
			case "tp":
				if (!isset($args[1])){
					//$o = TextFormat::RED . "You must specify an existing Area name";
                    $o = TextFormat::RED . Language::translate("specify-excisting-area-name");
					break;
				}
                if( isset( $this->areas[strtolower($args[1])] ) ){

                    $area = $this->areas[strtolower($args[1])];
                    $position = $sender->getPosition();
                    $perms = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[ $position->getLevel()->getName() ]["Perms"] : $this->perms);

                    if( $perms || $area->isWhitelisted($playerName) || $sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe.tp")){

                        $levelName = $area->getLevelName();
                        if(isset($levelName) && Server::getInstance()->loadLevel($levelName) != false){
                            $o = TextFormat::GREEN . Language::translate("tp-to-area-active") .' ' . $args[1];
                            $cx = $area->getSecondPosition()->getX() + ( ( $area->getFirstPosition()->getX() - $area->getSecondPosition()->getX() ) / 2 );
                            $cz = $area->getSecondPosition()->getZ() + ( ( $area->getFirstPosition()->getZ() - $area->getSecondPosition()->getZ() ) / 2 );
                            $cy1 = min( $area->getSecondPosition()->getY(), $area->getFirstPosition()->getY());
                            $cy2 = max( $area->getSecondPosition()->getY(), $area->getFirstPosition()->getY());
                            if( !$this->hasFallDamage($sender) ){
                                $this->playerTP[$playerName] = true; // player tp active $this->areaMessage( 'Fall save on!', $sender );
                            }
                            $sender->teleport( new Position( $cx, $cy2 - 2, $cz, $area->getLevel() ) );
                        }else{
                            $o = TextFormat::RED . Language::translate("the-level") . " " . $levelName . " " . Language::translate("for-area") ." ".  $args[1] ." ". Language::translate("cannot-be-found");
                        }
                    }else{
                        $o = TextFormat::RED .Language::translate("cmd-noperms-subcommand");
                    }
                }else{
                    $list = $this->listAllAreas();
                    $o = TextFormat::RED . Language::translate("the-area"). " " . $args[1] . " ". Language::translate("cannot-be-found"). $list;
                }
            break;

			case "f":
			case "flag":
			case "touch":
			case "pvp":
			case "flight":
			case "fly":
			case "animal":
			case "animals":
			case "mob":
			case "mobs":
			case "effect":
			case "effects":
			case "edit":
			case "god":
			case "msg":
			case "pass":
			case "passage":
			case "barrier":
			case "perm":
			case "perms":
			case "hunger":
			case "starve":
            case "fire":
            case "fires":
            case "burn":
			case "tnt":
			case "explode":
			case "explosion":
			case "explosions":
            case "shoot":
            case "launch":
			case "drop":
            case "falldamage":
            case "nofalldamage":
            case "fall":
            case "nfd":
            case "cmdmode":
            case "commandmode":
            case "cmdm":

				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") ||  $sender->hasPermission("festival.command.fe.flag")){
					if(isset($args[1])){
                        
						/**
						* Revert a flag in all area's (v1.0.4-11)
						*/
						if($args[1] == 'swappall'){
                            $flag = $this->isFlag( $args[0] ); // since v1.0.6-13
                            if( $flag ){
								foreach($this->areas as $area){
									if($area->getFlag($flag)){
										$area->setFlag($flag, false);
									}else{
										$area->setFlag($flag, true);
									}
								}
								$this->saveAreas();
								$o = TextFormat::RED . "All ". $flag ." flags for all areas have been swapped";
                                
                                
							}else{
								$o = TextFormat::RED . $flag ." is not a flag and can not be swapped";
							}  
                            
						}else if(isset($this->areas[strtolower($args[1])])){
							$area = $this->areas[strtolower($args[1])];
							$flag = $this->isFlag( $args[0] ); // v1.0.6-13
                            if( $flag ){
								if( isset($args[2]) && ( $args[2] == "true" ||  $args[2] == "on" ||  $args[2] == "false" ||  $args[2] == "off" ) ){
									$mode = strtolower($args[2]);
									if($mode === "true" || $mode === "on"){
										$mode = true;
									}else{
										$mode = false;
									}
									$area->setFlag($flag, $mode);
								}else{
									$area->toggleFlag($flag);
								}
								if($area->getFlag($flag)){
									$status = "on";
								}else{
									$status = "off"; 
								}
								$o = TextFormat::GREEN . Language::translate("flag") . " " . $flag . " ". Language::translate("set-to") . " " . $status . " ". Language::translate("for-area") . " " . $area->getName() . "!";
                                
							}else{

								if(isset($args[2])){ // excute long (old) notation
                                    if( $args[2] == "list" ){
                                        $flgs = $area->getFlags(); 
                                        $l = $area->getName() . TextFormat::GRAY . " " . Language::translate("flags"). ":";
                                        foreach($flgs as $fi => $flg){
                                            $l .= "\n". TextFormat::GOLD . "    ". $fi . ": ";
                                            if( $flg ){
                                                $l .= TextFormat::GREEN . Language::translate("status-on");
                                            }else{
                                                $l .= TextFormat::RED . Language::translate("status-off");
                                            }
                                        } 
                                        $o = $l;
                                    }else if( isset($area->flags[strtolower($args[2])]) ){
										$flag = strtolower($args[2]);
										if(isset($args[3])){
											$mode = strtolower($args[3]);
											if($mode === "true" || $mode === "on"){
												$mode = true;
											}else{
												$mode = false;
											}
											$area->setFlag($flag, $mode);
										}else{
											$area->toggleFlag($flag);
										}
										if($area->getFlag($flag)){
											$status = "on";
										}else{
											$status = "off";
										}
										//$o = TextFormat::GREEN . "Flag " . $flag . " set to " . $status . " for area " . $area->getName() . "!";
                                        $o = TextFormat::GREEN . Language::translate("flag") . " " . $flag . " ". Language::translate("set-to") . " " . $status . " ". Language::translate("for-area") . " " . $area->getName() . "!";
									}else{

										//$o = TextFormat::RED . "Flag not found. (Flags: god, pvp, flight, edit, touch, animals, mobs, effects, msg, passage, drop, tnt, shoot, hunger, perms, falldamage)";
                                        $o = TextFormat::RED . Language::translate("flag-not-found-list");
									}
								}else{
									//$o = TextFormat::RED . "Please specify a flag. (Flags: god, pvp, flight, edit, touch, animals, mobs, effects, msg, passage, drop, tnt, shoot, hunger, perms, falldamage)";
                                    $o = TextFormat::RED . Language::translate("flag-not-specified-list");
								}
							}
						}else{
                            $o = TextFormat::RED . Language::translate("area-not-excist"); //$o = TextFormat::RED . "Area doesn't exist.";
						}
					}else{
                        $o = TextFormat::RED . Language::translate("specify-to-flag");  //$o = TextFormat::RED . "Please specify the area you would like to flag.";
					}
				}else{
                    $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand"); //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
                break;

			case "del":
			case "delete":
			case "remove":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe.delete")){
					if(isset($args[1])){
						if(isset($this->areas[strtolower($args[1])])){
							$area = $this->areas[strtolower($args[1])];
							$area->delete();
                            $o = TextFormat::GREEN . Language::translate("area-deleted"); //$o = TextFormat::GREEN . "Area deleted!";
						}else{
                            $o = TextFormat::RED . Language::translate("area-not-excist"); //$o = TextFormat::RED . "Area does not exist.";
						}
					}else{
                        $o = TextFormat::RED . Language::translate("specify-to-delete"); //$o = TextFormat::RED . "Please specify an area to delete.";
					}
				}else{
                    $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand"); //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
                break;

			case "whitelist":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") ||  $sender->hasPermission("festival.command.fe.whitelist")){
					if(isset($args[1], $this->areas[strtolower($args[1])])){
						$area = $this->areas[strtolower($args[1])];
						if(isset($args[2])){
							$action = strtolower($args[2]);
							switch($action){
								case "add":
								$w = ($this->getServer()->getPlayer($args[3]) instanceof Player ? strtolower($this->getServer()->getPlayer($args[3])->getName()) : strtolower($args[3]));
								if(!$area->isWhitelisted($w)){
									$area->setWhitelisted($w);
									$o = TextFormat::GREEN . Language::translate("player"). " $w ". Language::translate("player-has-been-whitelisted")." " . $area->getName() . ".";
								}else{
									$o = TextFormat::RED . "Player $w ". Language::translate("player-allready-whitelisted")." " . $area->getName() . ".";
								}
								break;
								case "list":
								$o = TextFormat::AQUA .  Language::translate("area") . " " . $area->getName() . " ".Language::translate("area-whitelist").":" . TextFormat::RESET;
								foreach($area->getWhitelist() as $w){
									$o .= " $w;";
								}
								break;
								case "del":
								case "delete":
								case "remove":
								$w = ($this->getServer()->getPlayer($args[3]) instanceof Player ? strtolower($this->getServer()->getPlayer($args[3])->getName()) : strtolower($args[3]));
								if($area->isWhitelisted($w)){
									$area->setWhitelisted($w, false);
									$o = TextFormat::GREEN . Language::translate("player"). " $w ". Language::translate("player-has-been-unwhitelisted")." " . $area->getName() . ".";
								}else{
									$o = TextFormat::RED . Language::translate("player"). " $w ". Language::translate("player-allready-unwhitelisted")." " . $area->getName() . ".";
								}
								break;
								default:
								$o = TextFormat::RED . Language::translate("whitelist-specify-action");
								break;
							}
						}else{
							$o = TextFormat::RED . Language::translate("whitelist-specify-action");
						}
					}else{
						$o = TextFormat::RED . Language::translate("area-not-excist") . " ( /area whitelist <area> <add/list/remove> [player] )";
					}
				}else{
                    $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand"); //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
                break;

			case "c":
			case "cmd":
			case "command": /** /fe command <areaname> <add|list|edit|del> <commandindex> <commandstring>  */
				if( isset($args[1]) && (  $sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe.command") ) ){
					if( isset( $this->areas[strtolower($args[1])] ) ){
						if( isset($args[2]) ){
							$do = strtolower($args[2]);
							switch($do){
								case "add":
									$do = "enter";
								case "enter":
								case "leave":
								case "center":
									if( isset($args[3]) && isset($args[4]) ){
										$ar = $args[1];
										$cid = $args[3];
										unset($args[0]);
										unset($args[1]);
										unset($args[2]);
										unset($args[3]);
										$area = $this->areas[strtolower($ar)];
										$commandstring = implode(" ", $args);
										$cmds = $area->commands;
										if( count($cmds) == 0 || !isset($cmds[$cid]) ){
											if( isset($area->events[$do]) ){
												$eventarr = explode(",", $area->events[$do] );
												if(in_array($cid,$eventarr)){
													$o = TextFormat::RED . Language::translate("cmd-id").': ' . $cid . ' ' . Language::translate("allready-set-for-area") . ' ' . $do . Language::translate("-event") ;
												}else{
													$eventarr[] = $cid;
													$eventstr = implode(",", $eventarr );
													$area->events[$do] = $eventstr;
													$o = TextFormat::GREEN . Language::translate("cmd-id") .': ' . $cid . ' ' . Language::translate("set-for-area") . ' ' . $do . Language::translate("-event");
												}
											}else{
												$area->events[$do] = $cid;
												$o = TextFormat::RED .Language::translate("cmd-id").': ' . $cid . ' ' . Language::translate("set-for-area") . ' ' . $do . Language::translate("-event");
											}

											$area->commands[$cid] = $commandstring;
											$this->saveAreas();
											$o = TextFormat::GREEN . Language::translate("cmd-id") .' ' . $cid . ' ' . Language::translate("added-to-area") . ' ' .$ar;
										}else{
											$o = TextFormat::RED . Language::translate("cmd-id").': ' . $cid . ' ' . Language::translate("allready-set-for-area") . ' ' . $ar. ', ' . Language::translate("edit-id-or-other");
										}
									}else{
										$o = TextFormat::RED . Language::translate("cmd-specify-id-and-command-usage");
									}
                                    break;

								case "list":
									$ar = $this->areas[strtolower($args[1])];
									if( isset($ar->commands) ){
										$o = TextFormat::WHITE . $args[1] . TextFormat::AQUA .' '. Language::translate("cmd-list").': ';
										foreach($ar->events as $type => $list){
											if( trim($list,",") != "" ){
												$o .= "\n". TextFormat::YELLOW  . $type . ": ";
												$cmds = explode(",", trim($list,",") );
												foreach($cmds as $cmdid){
													if(isset($ar->commands[$cmdid])){
														$o .= "\n". TextFormat::LIGHT_PURPLE . $cmdid .": ". $ar->commands[$cmdid];
													}
												}
											}else{
												unset($this->areas[strtolower($args[1])]->events[$type]);
												$this->saveAreas();
											}
										}
									}
                                    break;

								case "event":
									if( isset($args[3]) && isset($args[4]) ){ //$o = '/fe command <eventname> event <COMMANDID> <EVENTTYPE>';
										$ar = $args[1];
										$area = $this->areas[strtolower($ar)];
										$cid = $args[3];
										$evt = strtolower($args[4]);
										$o = '';
										if( $evl = $area->getEvents() ){
											$ts = 0;
											foreach($evl as $t => $cids ){
												$arr = explode(",",$cids);
												if( in_array($cid,$arr) && $t != $evt){
													foreach($arr as $k => $ci){
														if($ci == $cid || $ci == ''){ // also remove empty values
															unset($arr[$k]);
														}
													}
													$area->events[$t] = trim( implode(",", $arr), ",");
													$ts = 1;
												}
												if( !in_array($cid,$arr) && $t == $evt){
													$arr[] = $cid;
													$area->events[$t] = trim( implode(",", $arr), ",");
													$ts = 1;
												}
											}
											if(!isset($evl[$evt])){
												// add new event type
												$area->events[$evt] = $cid;
												$ts = 1;
											}
											if($ts == 1){
												$this->saveAreas();
												$o = TextFormat::GREEN . Language::translate("cmd-id") .' '.$cid.' '. Language::translate("event-is-now").' '.$evt;
											}else{
												$o = TextFormat::RED . Language::translate("cmd-id") .' '.$cid.' '.Language::translate("event").' '.$evt.' '. Language::translate("change-failed");
											}
										}
									}
                                    break;

								case "edit":
									if( isset($args[3]) && isset($args[4]) ){
										$ar = $args[1];
										$cid = $args[3];
										unset($args[0]);
										unset($args[1]);
										unset($args[2]);
										unset($args[3]);
										$commandstring = implode(" ", $args);
										$area = $this->areas[strtolower($ar)];
										$cmds = $area->commands;
										if( isset($cmds[$cid]) ){
											$area->commands[$cid] = $commandstring;
											$this->saveAreas();
											$o = TextFormat::GREEN . Language::translate("cmd-id"). ' '.$cid.' edited';
										}else{
											$o = TextFormat::RED .Language::translate("cmd-id"). ' '.$cid.' '. Language::translate("cannot-be-found") .' ( /fe command <areaname> list)';
										}
									}else{
										$o = TextFormat::RED .Language::translate("cmd-specify-id-and-command-usage");
									}
                                    break;

								case "del":
								case "delete":
								case "remove":

									if( isset($args[3]) ){
										$area = $this->areas[strtolower($args[1])];
										$cid = $args[3];
										if( isset($area->commands[$cid]) ){
											if( isset($area->events) ){
												foreach($area->events as $e => $i){
													$evs = explode(",", $i);
													foreach($evs as $k => $ci){
														if($ci == $cid || $ci == ''){ //also remove empty values
															unset($evs[$k]);
														}
													}
													$str = trim( implode(",",$evs), ",");
													if( $str != ""){
														$area->events[$e] = $str;
													}else{
														unset($area->events[$e]);
													}
												}
											}
											unset($area->commands[$cid]);
											$this->saveAreas();
											$o = TextFormat::GREEN .'Command (id:'.$cid.') deleted';
										}else{
											//$o = TextFormat::RED .'Command ID not found. See the commands with /fe event command <areaname> list';
                                            $o = TextFormat::RED . Language::translate("cmd-id-not-found") . '.';
										}
									}else{
										//$o = TextFormat::RED .'Please specify the command ID to delete. Usage /fe event command <areaname> del <COMMANDID>';
                                        $o = TextFormat::RED . Language::translate("cmd-specify-id-to-delete") . '.';
									}
                                    break;

                                default:
								return false;
							}
						}else{
							//$o = TextFormat::RED . "Please add an action to perform with command.  Usage: /fe command <areaname> <add/list/edit/del> <commandID> <commandstring>.";
                            $o = TextFormat::RED . Language::translate("cmd-specify-action") . '.';
						}
					}else{
						//$o = TextFormat::RED . "Area not found, please submit a valid name. Usage: /fe command <areaname> <add/list/edit/del> <commandID> <commandstring>.";
                        $o = TextFormat::RED . Language::translate("cmd-valid-areaname") . '.';
					}
				}else{
					if(!isset($args[1])){
						//$o = TextFormat::RED . "Area not found, please submit a valid name. Usage: /fe command <areaname> <add/list/edit/del> <commandID> <commandstring>.";
                        $o = TextFormat::RED . Language::translate("cmd-valid-areaname") . '.';
					}else{
						//$o = TextFormat::RED . "You do not have permission to use this subcommand.";
                        $o = TextFormat::RED . Language::translate("cmd-noperms-subcommand");
					}
				}
                break;

			default:
            return false;
		}
		$sender->sendMessage($o);
		return true;
	}

    /** onJoin
      * set Area Titles for Player ( FloatingTextParticle )
	 * @param PlayerJoinEvent $event
	 */
    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $playername = strtolower($player->getName());
        $level = $player->getLevel(); //  $this->getServer()->getDefaultLevel();

        $this->areaTitles[$playername] = [];
        $this->inArea[$playername] = [];
        $this->checkAreaTitles( $player,  $level  );
    }



    /** onQuit
	 * @param Event $event
	 * @return bool
	 */
    public function onQuit(PlayerQuitEvent $event){

        $playerName = strtolower($event->getPlayer()->getName());
        $lvl = $event->getPlayer()->getLevel()->getName();
        unset($this->inArea[$playerName]);

        foreach($this->areas as $area){
            $this->hideAreaTitle( $event->getPlayer(), $event->getPlayer()->getPosition()->getLevel(), $area );
        }
        unset( $this->areaTitles[$playerName] );

    }

    /** levelChange
     * change Area Titles for Player ( FloatingTextParticle )
	 * @param EntityLevelChangeEvent $event
	 */
    public function levelChange(EntityLevelChangeEvent $event) {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $level = $event->getTarget();
            $this->checkAreaTitles( $entity, $level );
        }
    }
    
	/** onMove
	 * @param PlayerMoveEvent $ev
	 * @var string inArea
	 * @return true
	 */
	public function onMove(PlayerMoveEvent $ev) : void{
		$player = $ev->getPlayer();
		$playerName = strtolower( $player->getName() );
		if( !isset( $this->inArea[$playerName] ) ){
			$this->inArea[$playerName] = [];
		}
		foreach($this->areas as $area){
            if( $area->getFlag("passage") ){ // Player area passage
				if( $player->isOp() || $area->isWhitelisted( strtolower( $player->getName() )  ) || $player->hasPermission("festival") || $player->hasPermission("festival.access") ){
					if( ( $area->contains( $player->getPosition(), $player->getLevel()->getName() ) && !$area->contains( $ev->getFrom(), $player->getLevel()->getName() ) )
					|| !$area->contains( $player->getPosition(), $player->getLevel()->getName() ) && $area->contains( $ev->getFrom(), $player->getLevel()->getName() ) ){
						// ops & whitelist players pass
						$this->barrierCrossByOp($area, $ev);
						break;
					}
				}else{
					if( $area->contains( $player->getPosition(), $player->getLevel()->getName() )
					&& !$area->contains( $ev->getFrom(), $player->getLevel()->getName() ) ){
						$this->barrierEnterArea($area, $ev);
						break;
					}
					if( !$area->contains( $player->getPosition(), $player->getLevel()->getName() )
					&& $area->contains( $ev->getFrom(), $player->getLevel()->getName() ) ){
						$this->barrierLeaveArea($area, $ev);
						break;
					}
				}
			}
            // Player enter or leave area
			if( !$area->contains( $player->getPosition(), $player->getLevel()->getName() ) ){
                // Player leave Area
				if( in_array( strtolower( $area->getName() ) , $this->inArea[$playerName] ) ){
					$this->leaveArea($area, $ev);
					break;
				}
			}else{
                // Player enter Area
				if( !in_array( strtolower( $area->getName() ), $this->inArea[$playerName] ) ){
					$this->enterArea($area, $ev);
					break;
				}
                // Player enter Area Center
				if( $area->centerContains( $player->getPosition(), $player->getLevel()->getName() ) ){
					if( !in_array( strtolower( $area->getName() )."center", $this->inArea[$playerName] ) ){ // Player enter in Area
						$this->enterAreaCenter($area, $ev);
						break;
					}
				}else{
                    // Player leave Area Center
					if( in_array( strtolower( $area->getName()."center" ) , $this->inArea[$playerName] ) ){
						$this->leaveAreaCenter($area, $ev);
						break;
					}
				}
			}
            // Area Player Monitor  $this->AreaPlayerMonitor($area, $ev);
		}
        $this->checkPlayerFlying( $ev->getPlayer() );
		return;
	}

	/** Block Place
	 * @param BlockPlaceEvent $event
	 * @ignoreCancelled true
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$playerName = strtolower($player->getName());
		if(isset($this->selectingFirst[$playerName])){
			unset($this->selectingFirst[$playerName]);
			$this->firstPosition[$playerName] = $block->asVector3();
			$player->sendMessage(TextFormat::GREEN . language::translate("pos1")." ". language::translate("set-to"). ": (" . $block->getX() . ", " . $block->getY() . ", " . $block->getZ() . ")");
			$event->setCancelled();
		}elseif(isset($this->selectingSecond[$playerName])){
			unset($this->selectingSecond[$playerName]);
			$this->secondPosition[$playerName] = $block->asVector3();
			$player->sendMessage(TextFormat::GREEN . language::translate("pos2")." ". language::translate("set-to"). ": (" . $block->getX() . ", " . $block->getY() . ", " . $block->getZ() . ")");
			$event->setCancelled();
		}else{
            //  .. canUseTNT( $player, $block )
            if( $block->getID() == Block::TNT && !$this->canUseTNT( $player, $block ) ){
                if( $player->hasPermission("festival") || $player->hasPermission("festival.access") ){
		        }else{
                    $event->setCancelled();
                    //$player->sendMessage("TNT not allowed here");
                }
            }

			if(!$this->canEdit($player, $block)){
				$event->setCancelled();
			}
		}
	}

	/** Block break
	 * @param BlockBreakEvent $event
	 * @ignoreCancelled true
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$playerName = strtolower($player->getName());
		if(isset($this->selectingFirst[$playerName])){
			unset($this->selectingFirst[$playerName]);
			$this->firstPosition[$playerName] = $block->asVector3();
			$player->sendMessage(TextFormat::GREEN . language::translate("pos1")." ". language::translate("set-to"). ": (" . $block->getX() . ", " . $block->getY() . ", " . $block->getZ() . ")");
			$event->setCancelled();
		}elseif(isset($this->selectingSecond[$playerName])){
			unset($this->selectingSecond[$playerName]);
			$this->secondPosition[$playerName] = $block->asVector3();
			$player->sendMessage(TextFormat::GREEN . language::translate("pos2")." ". language::translate("set-to"). ": (" . $block->getX() . ", " . $block->getY() . ", " . $block->getZ() . ")");
			$event->setCancelled();
		}else{
			if(!$this->canEdit($player, $block)){
				$event->setCancelled();
			}
		}
	}

	/** onBlockTouch
	 * @param PlayerInteractEvent $event
	 * @ignoreCancelled true
	 */
	public function onBlockTouch(PlayerInteractEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();
		if(!$this->canTouch($player, $block)){
			$event->setCancelled();
		}
	}

	/** onInteract
	 * @param PlayerInteractEvent $event
	 * @ignoreCancelled true
	 */
    public function onInteract( PlayerInteractEvent $event ): void{
        if ( !$this->canInteract( $event ) ) {
            $event->setCancelled();
        }
    }

    /** onBlockUpdate
     * BlockUpdateEvent
     * @param BlockUpdateEvent $event
     * @return void
     */
    public function onBlockUpdate( BlockUpdateEvent $event ): void{ // BlockUpdateEvent

        $block = $event->getBlock();
        $position = new Position($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), $block->getLevel());
        $levelname = $block->getLevel()->getName();

        // kill fire -  or lava -  flowing_lava 10, lava 11 , Bucket item id 325
        $f = true;
        $aid = $block->getLevel()->getBlockIdAt($block->x, $block->y + 1, $block->z);

        if(  $aid == 51 ||  $aid == 10 || $aid == 11 ){ // is fire/lava above
            if( !$this->canBurn( $position ) ){ // is fire not allowed? // Block::FIRE
                $f = false;
                $event->setCancelled();
            }
        }
    }


    /** onBlockBurn
     * BlockBurnEvent
     * @param BlockUpdateEvent $event
     * @return void

        // Should check BlockBurnEvent ..
        // https://github.com/pmmp/PocketMine-MP/blob/master/src/pocketmine/event/block/BlockBurnEvent.php

    public function onBlockBurn( BlockBurnEvent $event ): void { // BlockBurnEvent

        $block = $event->getBlock();
        $position = new Position($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), $block->getLevel());
        $levelname = $block->getLevel()->getName();

        if( !$this->canBurn( $position ) ){ // is fire not allowed? // Block::FIRE
            $event->setCancelled();
        }

    }
     */


    /*
    public function onPlayerBucketEvent( PlayerBucketEvent $event): void{
        $block = $event->getBlockClicked();
        $position = new Position($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), $block->getLevel());
        if( ($event->getItem()->getId() == 10 || $event->getItem()->getId() == 11) && !$this->canBurn( $position ) ){
			$event->setCancelled();
            $this->getLogger()->info( 'No lava bucket allowed!' );
        }
    }*/

	/** onHurt
	 * @param EntityDamageEvent $event
	 * @ignoreCancelled true
	 */
	public function onHurt(EntityDamageEvent $event) : void{
		$this->canDamage( $event );
	}

	/** onDamage
	 * @param EntityDamageEvent $event
	 * @ignoreCancelled true
	 */
	public function onDamage(EntityDamageEvent $event) : void{
		$this->canDamage( $event );
	}

    /** Mob / Animal spawning
	 * @param EntitySpawnEvent $event
	 * @ignoreCancelled true
     */
    public function onEntitySpawn( EntitySpawnEvent $event ): void{

        $e = $event->getEntity();
        //($e instanceof Fire && !$this->canBurn( $e->getPosition() )) || (
        if( !$e instanceof Player && !$this->canEntitySpawn( $e ) ){
            //$e->flagForDespawn() to slow / ? $e->close(); private..
            $this->getServer()->getPluginManager()->callEvent(new EntityDespawnEvent($e));
            $e->despawnFromAll();
            if($e->chunk !== null){
                $e->chunk->removeEntity($e);
                $e->chunk = null;
            }
            if($e->isValid()){
                $e->level->removeEntity($e);
                $e->setLevel(null);
            }
        }

    }

	/** Item drop
	 * @param itemDropEvent $event
	 * @ignoreCancelled true
	 */
	public function onDrop(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		$position = $player->getPosition();
		if(!$this->canDrop($player, $position)){
			$event->setCancelled();
			return;
		}
	}

    /** Shoot / Launch projectiles
	 * @param EntityShootBowEvent $event
	 * @ignoreCancelled true
     */
    public function onEntityShootBow( EntityShootBowEvent $event ){
        $e = $event->getEntity();
        if( $e instanceof Player){
            if( !$this->canShoot($e) ){
                $event->setCancelled();
            }
        }
    }

    /** on Explode entity // canExplode
     * EntityExplodeEvent
     * @param EntityExplodeEvent $event
     * @return void
     */
    public function onEntityExplode(EntityExplodeEvent $event){

        $e = $event->getEntity();
        if( $e instanceof PrimedTNT ){
            if( !$this->canTNTExplode( $event->getPosition() ) ){
                $event->setCancelled(); // ? on
            }
        }else if (!$this->canExplode( $event->getPosition() )) {
            $event->setCancelled();
        }

    }


    /** Hunger
     * PlayerExhaustEvent
     * @param PlayerExhaustEvent $event
     * @return void
     */
    public function Hunger(PlayerExhaustEvent $event){
        if ( !$this->canHunger( $event ) ) {
            $event->setCancelled();
        }
    }



    /** OUTBOUND ACTION */

	/** canEdit
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
	 */
	public function canEdit(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
		$o = true;
		$e = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Edit"] : $this->edit);
		if($e){
			$o = false;
		}

        $playername = strtolower($player->getName());

        foreach ($this->areas as $area) {
            if ($area->contains(new Vector3($position->getX(), $position->getY(), $position->getZ()), $position->getLevel()->getName() )) {
                if($area->getFlag("edit")){
                    $o = false;
                }
                if(!$area->getFlag("edit") && $e){
                    $o = true;
                }
                if($area->isWhitelisted($playername)){
                    $o = true;
                }
            }
        }
        /*
        foreach($this->inArea[$playername] as $areaname){
            if( isset($this->areaList[ $areaname ]) ){
                $area = $this->areaList[$areaname];
                if($area->getFlag("edit")){
                    $o = false;
                }
                if(!$area->getFlag("edit") && $e){
                    $o = true;
                }
                if($area->isWhitelisted($playername)){
                    $o = true;
                }
            }
        }
        */
		return $o;
	}

	/** canTouch
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
	 */
	public function canTouch(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
        $playername = strtolower($player->getName());
		$o = true;

		$t = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Touch"] : $this->touch);
		if($t){
			$o = false;
		}

        foreach ($this->areas as $area) {
            if ($area->contains(new Vector3($position->getX(), $position->getY(), $position->getZ()), $position->getLevel()->getName() )) {
                if($area->getFlag("touch")){
                    $o = false;
                }
                if(!$area->getFlag("touch") && $t){
                    $o = true;
                }
                if($area->isWhitelisted($playername)){
                    $o = true;
                }
            }
        }

        /*
        if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
            foreach($this->inArea[$playername] as $areaname){
                if( isset($this->areaList[ $areaname ]) ){
                    $area = $this->areaList[$areaname];
                    if($area->getFlag("touch")){
                        $o = false;
                    }
                    if(!$area->getFlag("touch") && $t){
                        $o = true;
                    }
                    if($area->isWhitelisted($playername)){
                        $o = true;
                    }
                }
            }
        }else{
            $o = false;
        }*/

		return $o;
	}

    /** canInteract
     * @param PlayerInteractEvent $event
     * @return bool
     */
    public function canInteract( PlayerInteractEvent $event ): bool{
        
        $item = $event->getItem();
        $block = $event->getBlock();
		$player = $event->getPlayer();
        $position = new Position($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), $block->getLevel());// $player->getPosition();
        $playername = strtolower($player->getName());
        $b = $block->getID();
        $i = $item->getID();

        // $player->sendMessage("Action on ".$block->getName()."(".$block->getID().") with ".$item->getName()."(".$item->getID().") at [x=".round($block->x). " y=".round($block->y)." z=".round($block->z)."]");

        if( $player->isOp() || $player->hasPermission("festival") || $player->hasPermission("festival.access")){
            return true;
        }

        // tnt flag id 46 Block::TNT _ id 259 FLINT_AND_STEEL ? canUseTNT( $player, $b )
        if( $b == 46 && $i == 259 && !$this->canUseTNT( $player, $position ) ){
            return false;
        }

        // fire flag - see also onBlockUpdate
        if( $i == 259 && $b != 46 && !$this->canBurn( $position ) ){ // FLINT_AND_STEEL + not tnt
            return false;
        }

        // edit flag for items - 199 itemframe, dirt & grass + items for farm events
        $o = true;
        if( ( $b == 199 || ( ( $b == 2 || $b == 3) && ( $i == 290 || $i == 291 || $i == 292 || $i == 293 || $i == 294 ) ) ) && !$this->canEdit($player, $block) ){
            $o = false;
        }
        return $o;

    }

	/** Hurt
	 * @param Entity $entity
	 * @return bool
	 */
	public function canGetHurt(Entity $entity) : bool{
		$o = true;
        if( $entity instanceof Player){
            $g = (isset($this->levels[$entity->getLevel()->getName()]) ? $this->levels[$entity->getLevel()->getName()]["God"] : $this->god);
            if($g){
                $o = false;
            }
            $playername =  strtolower($entity->getName());
            if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
                foreach($this->inArea[$playername] as $areaname){
                    if( isset($this->areaList[ $areaname ]) ){
                        $area = $this->areaList[ $areaname ];
                        if($area->getFlag("god")){
                            $o = false;
                        }
                        if(!$area->getFlag("god") && $g ){
                            $o = true;
                        }
                        if($area->isWhitelisted($playername)){
                            $o = false;
                        }
                    }
                }
            }else{
               $o = false;
            }
        }
		return $o;
	}

	/** On No fall Damage
	 * @param Entity $entity
	 * @return bool
	 */
	public function hasFallDamage(Entity $entity) : bool{

		$o = true;
        if( $entity instanceof Player ){
            $f = (isset($this->levels[$entity->getLevel()->getName()]) ? $this->levels[$entity->getLevel()->getName()]["FallDamage"] : $this->falldamage);
            if($f){
                $o = false;
            }
            $playername = strtolower($entity->getName());
            if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
                foreach($this->inArea[$playername] as $areaname){
                    if( isset($this->areaList[ $areaname ]) ){
                        $area = $this->areaList[$areaname];
                        if($area->getFlag("falldamage")){
                            $o = false;
                        }
                        if(!$area->getFlag("falldamage") && $f){
                            $o = true;
                        }
                        if($area->isWhitelisted($playername)){
                            $o = false;
                        }
                    }
                }
            }else{
                $o = false;
            }
        }
		return $o;
	}

    /** PVP
	 * @param Event $ev
	 * @return bool
	 */
	public function canPVP(EntityDamageEvent $ev) : bool{
        $o = true;
        $god = false;
        if($ev instanceof EntityDamageByEntityEvent){
            if($ev->getEntity() instanceof Player && $ev->getDamager() instanceof Player){
                $entity = $ev->getEntity();
                $p = (isset($this->levels[$entity->getLevel()->getName()]) ? $this->levels[$entity->getLevel()->getName()]["PVP"] : $this->pvp);
                if($p){
                    $o = false;
                }
                $playername = $entity->getName();
                $pos = $entity->getPosition();
                foreach ($this->areas as $area) {
                    if ($area->contains(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $pos->getLevel()->getName() )) {
                        $god = $area->getFlag("god");
                        if($area->getFlag("pvp")){
                            $o = false;
                        }
                        if( !$area->getFlag("pvp") && $p){
                            $o = true;
                        }
                        if($area->isWhitelisted($playername)){
                            $o = false;
                        }
                    }
                }
            }
        }
        if( !$o ){
            $player = $ev->getDamager();
            if( $this->skippTime( 2, strtolower($player->getName()) ) ){
                if( $god ){
                    $this->areaMessage( Language::translate("all-players-are-god"), $player );
                }else{
                    $this->areaMessage( Language::translate("no-pvp-area"), $player );
                }
			}
        }
		return $o;
    }

    /** Player Damage Impact
	 * @param EntityDamageEvent $event
	 * @ignoreCancelled true
     */
	public function canDamage(EntityDamageEvent $ev) : bool{

        if($ev->getEntity() instanceof Player){
			$player = $ev->getEntity();
			$playerName = strtolower($player->getName());
			if( !$this->canGetHurt( $player ) ){
                if( $player->isOnFire() ){
                    $player->extinguish(); // 1.0.7-dev
                }
				$ev->setCancelled();
                return false;
			}
            if( !$this->canBurn( $player->getPosition() )){
                if( $player->isOnFire() ){
                    $player->extinguish(); // 1.0.7-dev
				    $ev->setCancelled();
                    return false;
                }
			}
            if(!$this->canPVP($ev)){ // v 1.0.6-13
				$ev->setCancelled();
                return false;
			}
			if( isset($this->playerTP[$playerName]) && $this->playerTP[$playerName] == true ){
				unset( $this->playerTP[$playerName] ); //$this->areaMessage( 'Fall save off', $player );
				$ev->setCancelled();
                return false;
			}
		}
        return true;

    }

    /** Flight
	 * @param Player $player
     */
    public function checkPlayerFlying(Player $player){

        $fly = true;
        $sendmsg = false;
        $falldamage = false;
		$position = $player->getPosition();
        $playername = strtolower($player->getName());

        $f = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Flight"] : $this->flight);
        if( $f ){
            $fly = false; // flag default
        }
        if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
            foreach($this->inArea[$playername] as $areaname){
                if( isset($this->areaList[ $areaname ]) ){
                    $area = $this->areaList[$areaname];
                    if(  $area->getFlag("flight") && !$area->isWhitelisted( $playername ) ){
                        $fly = false; // flag area
                    }
                    if(!$area->getFlag("flight") && $f){
                        $fly = true;
                    }
                    if( !$area->getFlag("msg") ){
                        $sendmsg = true;
                    }
                    if( $area->getFlag("falldamage") ){
                        $falldamage = true;
                    }
                }
            }
        }else{
            $fly = false; // flag default
        }
        // ! if( $player->isOp() ){
        // Survival Mode = 0, Creative Mode = 1, Adventure Mode = 2, Spectator Mode = 4
        if( $player->hasPermission("festival") || $player->hasPermission("festival.access") || $player->getGamemode() === 1 ){
            $fly = true;
            $player->setAllowFlight(true);
            return $fly;
        }

        $msg = '';
        if( !$fly && $player->isFlying() ){
            if( $falldamage ){
            $this->playerTP[ strtolower( $player->getName() ) ] = true; // player tp active (fall save)
            }
            $player->setFlying(false);
            //$player->sendMessage(  TextFormat::RED . "NO Flying here!" );
            if( $sendmsg ){
                $msg = TextFormat::RED . Language::translate("no-flight-area");
                $player->sendMessage( $msg );
            }
        }
        if( $fly && !$player->isFlying() && !$player->getAllowFlight() ){
            if( $sendmsg ){
                $msg = TextFormat::GREEN . Language::translate("flight-area");
                $player->sendMessage( $msg );
            }
        }
        $player->setAllowFlight($fly);
        return $fly;

    }

    /** canEntitySpawn
	 * @param Entity $e
	 * @return bool
    */
    public function canEntitySpawn( Entity $e ): bool{

        $o = true;
        if( // what entities are always allowed
            $e instanceof FallingBlock // FallingBlock (Sand,Gravel, Water, Lava? )// $e instanceof FallingSand
            || $e instanceof PrimedTNT
            || $e instanceof ExperienceOrb
            || $e instanceof ItemEntity
            || $e instanceof Projectile
            || $e instanceof FloatingTextParticle

            //|| $e instanceof mysterybox\entity\MysterySkull // https://github.com/CubePM/MysteryBox/blob/master/src/mysterybox/entity/MysterySkull.php
        ){
            return $o; // might be allowed to spawn under different flag
        }
        
        $nm =  ''; //
        if( method_exists($e,'getName') && null !== $e->getName() ){
          $nm = $e instanceof Item ? $e->getItem()->getName() : $e->getName();
        }
        $pos = false;
        if( method_exists($e,'getPosition') && null !== $e->getPosition() ){
            $pos = $e->getPosition();
        }

        if($pos && $nm != ''){

            $animals =[ 'bat','chicken','cow','horse','llama','donkey','mule','ocelot','parrot','fish','dolphin','squit','pig','rabbit','sheep','pufferfish','salmon','turtle','tropical_fish','cod','balloon'];

            if( in_array( strtolower($nm), $animals ) ){
                // check animal flag
                $a = (isset($this->levels[$pos->getLevel()->getName()]) ? $this->levels[$pos->getLevel()->getName()]["Animals"] : $this->animals);
                if ($a) {
                    $o = false;
                }
                foreach ($this->areas as $area) {
                    if ($area->contains(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $pos->getLevel()->getName() )) {
                        if ($area->getFlag("animals")) {
                            $o = false;
                        }
                        if(!$area->getFlag("animals") && $a){
                            $o = true;
                        }
                    }
                }
            }else{
                // check mob flag
                $m = (isset($this->levels[$pos->getLevel()->getName()]) ? $this->levels[$pos->getLevel()->getName()]["Mobs"] : $this->mobs);
                if ($m) {
                    $o = false;
                }
                foreach ($this->areas as $area) {
                    if ($area->contains(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $pos->getLevel()->getName() )) {

                        if ($area->getFlag("mobs")) {
                            $o = false;
                        }
                        if(!$area->getFlag("mobs") && $m){
                            $o = true;
                        }
                    }
                }
            }
        }
        /* if($o){
            $this->getLogger()->info( 'Spawn '.$nm.' entity allowed' );
        }else{
            $this->getLogger()->info( 'Spawn '.$nm.' entity canceled' );
        } */
        return $o;
    }

    /** Effects
	 * @param Player $player
	 * @return bool
     */
    public function canUseEffects( Player $player ) : bool{

		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}

        $position = $player->getPosition();
        $playername = strtolower($player->getName());
		$o = true;
		$e = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Effects"] : $this->effects);
		if($e){
			$o = false;
		}

        if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
            foreach($this->inArea[$playername] as $areaname){
                if( isset($this->areaList[ $areaname ]) ){
                    $area = $this->areaList[$areaname];
                    if($area->getFlag("effects")){
                        $o = false;
                    }
                    if(!$area->getFlag("effects") && $e){
                        $o = true;
                    }
                    if( $area->isWhitelisted( $playername ) ){
                        $o = true;
                    }
                }
            }
        }else{
            $o = false;
        }
		return $o;
	}

	/** canDrop
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
	 */
	public function canDrop(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
		$o = true;
		$d = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Drop"] : $this->drop);
		if($d){
			$o = false;
		}
        $playername = strtolower($player->getName());

        if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
            foreach($this->inArea[$playername] as $areaname){
                if( isset($this->areaList[ $areaname ]) ){
                    $area = $this->areaList[$areaname];
                    if($area->getFlag("drop")){
                        $o = false;
                    }
                    if(!$area->getFlag("drop") && $d){
                        $o = true;
                    }
                    if($area->isWhitelisted($playername)){
                        $o = true;
                    }
                }
            }
        }else{
            $o = false;
        }
		return $o;
	}

    /**
     * canBurn()
     * Checks if fire is allowed on given position
     * @param flag $this->fire
     * @param pocketmine\level\Position $pos
     * @param pocketmine\level\Level $level
     * @return bool
     */
    public function canBurn( Position $pos ): bool{
        $o = true;
        $e = (isset($this->levels[$pos->getLevel()->getName()]) ? $this->levels[$pos->getLevel()->getName()]["Fire"] : $this->fire);
        if ($e) {
            $o = false;
        }
        // including entities/mobs in any area
        foreach ($this->areas as $area) {
            if ($area->contains(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $pos->getLevel()->getName() )) {
                if ($area->getFlag("fire")) {
                    $o = false;
                }
                if(!$area->getFlag("fire") && $e){
                    $o = true;
                }
            }
        }
        return $o;
    }


    /**
     * canExplode()
     * Checks if entity can explode on given position
     * @param pocketmine\level\Position $pos
     * @param pocketmine\level\Level $level
     * @return bool
     */
    public function canExplode( Position $pos ): bool{

        $o = true;
        $e = (isset($this->levels[$pos->getLevel()->getName()]) ? $this->levels[$pos->getLevel()->getName()]["Explode"] : $this->explode);
        if ($e) {
            $o = false;
        }
        // including entities/mobs in any area
        foreach ($this->areas as $area) {
            if ($area->contains(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $pos->getLevel()->getName() )) {
                if ($area->getFlag("explode")) {
                    $o = false;
                }
                if(!$area->getFlag("explode") && $e){
                    $o = true;
                }
            }
        }
        return $o;
    }

	/** canUseTNT()
     * Checks if TNT is allowed to be used by player on given position
     * @param flag $this->tnt
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
    */
	public function canUseTNT(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
		$o = true;
		$d = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["TNT"] : $this->tnt);
		if($d){
			$o = false;
		}
        $playername = strtolower($player->getName());

        foreach ($this->areas as $area) {
            if ($area->contains(new Vector3($position->getX(), $position->getY(), $position->getZ()), $position->getLevel()->getName() )) {
                if($area->getFlag("tnt")){
                    $o = false;
                }
                if(!$area->getFlag("tnt") && $d){
                    $o = true;
                }
                if($area->isWhitelisted($playername)){
                    $o = true;
                }
            }
        }
        /*
        if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
            foreach($this->inArea[$playername] as $areaname){
                if( isset($this->areaList[ $areaname ]) ){
                    $area = $this->areaList[$areaname];

                    //if( $area->contains( $position, $position->getLevel()->getName() ) ){}

                    if($area->getFlag("tnt")){
                        $o = false;
                    }
                    if(!$area->getFlag("tnt") && $d){
                        $o = true;
                    }
                    if($area->isWhitelisted($playername)){
                        $o = true;
                    }
                }
            }
        }else{
            $o = false;
        }
        */
		return $o;
	}

    /** canTNTExplode()
     * Checks if TNT is allowed to be used by player on given position
     * @param flag $this->tnt
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
    */
	public function canTNTExplode( Position $position ) : bool{

		$o = true;
        $e = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["TNT"] : $this->tnt);
        if ($e) {
            $o = false;
        }
        // look for any area
        foreach ($this->areas as $area) {
            if ($area->contains(new Vector3($position->getX(), $position->getY(), $position->getZ()), $position->getLevel()->getName() )) {
                if ($area->getFlag("tnt")) {
                    $o = false;
                }
                if(!$area->getFlag("tnt") && $e){
                    $o = true;
                }
            }
        }
        return $o;
	}


    /** canShoot
	 * @param Player $player
	 * @return bool
     */
    public function canShoot( Player $player ) : bool{

		if( $player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}

        $position = $player->getPosition();
        $playername = strtolower($player->getName());
		$o = true;
        $m = true;
		$s = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Shoot"] : $this->shoot);
		if($s){
			$o = false;
		}

        if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
            foreach($this->inArea[$playername] as $areaname){
                if( isset($this->areaList[ $areaname ]) ){
                    $area = $this->areaList[$areaname];
                    if($area->getFlag("shoot")){
                        $o = false;
                    }
                    if(!$area->getFlag("shoot") && $s){
                        $o = true;
                    }
                    if($area->isWhitelisted($playername)){
                        $o = true;
                    }
                    if( $area->getFlag("msg") ){
                       $m = false;
                    }
                }
            }
        }else{
            $o = false;
            $m = true;
        }
        if( $m && !$o ){ // 'ínline' message method
            $msg = TextFormat::RED . "NO Shooting here!";
            $player->areaMessage( $msg );
        }
		return $o;

	}


	/** useOpPerms
	 * @param Player $player
	 * @param Area $area
	 * @return bool
	 */
	public function useOpPerms(Player $player, Area $area) : bool{

		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true; // festival ops..
		}

		$position = $player->getPosition();
		$o = true;
		$p = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[ $position->getLevel()->getName() ]["Perms"] : $this->perms);
		if($p){
			$o = false;
		}
		if( $area->getFlag("perms") ){
			$o = false;
		}
        if(!$area->getFlag("perms") && $p){
            $o = true;
        }
		if( $area->isWhitelisted( strtolower( $player->getName() ) ) ){
			$o = true;
		}
		return $o;
	}

    /**
     * canhunger()
     * Checks if player can exhaust  (hunger)
     * @param pocketmine\level\Position $pos
     * @param pocketmine\level\Level $level
     * @return bool
     */
    public function canHunger( PlayerExhaustEvent $event ): bool{
        $pos = $event->getPlayer()->getPosition();
        $playername = strtolower($event->getPlayer()->getName());
        $o = true;
        $h = (isset($this->levels[$pos->getLevel()->getName()]) ? $this->levels[$pos->getLevel()->getName()]["Hunger"] : $this->hunger);
        if ($h) {
            $o = false;
        }
        if( isset( $this->inArea[$playername] ) && is_array( $this->inArea[$playername] ) ){
            foreach($this->inArea[$playername] as $areaname){
                if( isset($this->areaList[ $areaname ]) ){
                    $area = $this->areaList[$areaname];
                    if ($area->getFlag("hunger")) {
                        $o = false;
                    }
                    if(!$area->getFlag("hunger") && $h){
                        $o = true;
                    }
                    if($area->isWhitelisted($playername)){
                        $o = false;
                    }
                }
            }
        }else{
            $o = false;
        }
        return $o;
    }



	/** Area Player Monitor/Task
	 * @param area Area
	 * @param PlayerMoveEvent $ev 
	 * Set/refresh effects & status
	 */
    public function AreaPlayerMonitor( Area $area, PlayerMoveEvent $ev ): void{
        $player = $ev->getPlayer();
        if( $area->contains( $player->getPosition(), $player->getLevel()->getName() ) ){ 
            if( $this->skippTime(5, strtolower($player->getName()) ) ){ 
                // start / renew effects
                //$msg = TextFormat::YELLOW . "Time passing in area " . $area->getName();
                //$this->areaMessage( $msg, $player );
            }
        }
    }
    
	/** Area event barrier cross by op
	 * @param area Area
	 * @param PlayerMoveEvent $ev 
	 * @return false
	 */
	public function barrierCrossByOp(Area $area, PlayerMoveEvent $ev): void{
		$player = $ev->getPlayer();
		if( $this->msgOpDsp( $area, $player ) ){
			$msg = TextFormat::WHITE . $area->getName(). TextFormat::RED . " " . Language::translate("enter-barrier-area");
			$player->areaMessage( $msg );
		}
		return; 
	}
	
	/**
	 * Area event barrier enter
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @return false 
	 */
	public function barrierEnterArea(Area $area, PlayerMoveEvent $ev): void{
		$player = $ev->getPlayer();
		$ev->getPlayer()->teleport($ev->getFrom());
		if( !$area->getFlag("msg")  || $this->msgOpDsp( $area, $player ) ){
			if( $this->skippTime( 2, strtolower($player->getName()) ) ){
				$msg = TextFormat::YELLOW . Language::translate("cannot-enter-area") . " " . $area->getName();
				$this->areaMessage( $msg, $player );
			}
		}
		return;
	}

	/** Area event barrier leave
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @return false
	 */
	public function barrierLeaveArea(Area $area, PlayerMoveEvent $ev): void{
		$player = $ev->getPlayer();
		$msg = '';
		$ev->getPlayer()->teleport($ev->getFrom());
		if( !$area->getFlag("msg")  || $this->msgOpDsp( $area, $player ) ){
			if( $this->skippTime( 2, strtolower($player->getName()) ) ){ 
				$msg = TextFormat::YELLOW . Language::translate("cannot-leave-area") . " " . $area->getName();
			}
			if( $msg != ''){
				$this->areaMessage( $msg, $player );
			}
		}
		return;
	}

	/** Area event enter
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @return false
	 */
	public function enterArea(Area $area, PlayerMoveEvent $ev): void{
		$player = $ev->getPlayer();
		$msg = '';
		if( !$area->getFlag("msg")  || $this->msgOpDsp( $area, $player ) ){
			$msg = TextFormat::AQUA . $player->getName() . " " .Language::translate("enter-area") . " " . $area->getName();
			if( $area->getDesc() ){
				$msg .= "\n". TextFormat::WHITE . $area->getDesc();
			}
			if( $msg != ''){
				$this->areaMessage( $msg, $player );
			} 
		}
		$playerName = strtolower( $player->getName() );

		$this->inArea[$playerName][] = strtolower( $area->getName() ); // player area's

        // effects check
        if( $this->canUseEffects( $player ) ){// use effects
        }else{
            foreach ($player->getEffects() as $effect) {
                $player->removeEffect($effect->getId());
            }
        }
		$this->runAreaEvent($area, $ev, "enter"); 
		return;
	}

	/** Area event leave
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @return false
	 */
	public function leaveArea(Area $area, PlayerMoveEvent $ev): void{
		$player = $ev->getPlayer();
		$msg = '';
		if( !$area->getFlag("msg") || $this->msgOpDsp( $area, $player ) ){
			$msg .= TextFormat::YELLOW . $player->getName() . " " .Language::translate("leaving-area") . " " . $area->getName();
		}
		if( $msg != ''){
			$this->areaMessage( $msg, $player );
		}

		$playerName = strtolower( $player->getName() );
		if (($key = array_search( strtolower( $area->getName() ), $this->inArea[$playerName] )) !== false) {
			unset($this->inArea[$playerName][$key]);
		}
		$this->runAreaEvent($area, $ev, "leave");
		return;
	}

	/** Area event enter center
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @return false
	 */
	public function enterAreaCenter(Area $area, PlayerMoveEvent $ev): void{
		// in area center
		$player = $ev->getPlayer();
		$msg = '';
		if( !$area->getFlag("msg")  || $this->msgOpDsp( $area, $player ) ){
			$msg = TextFormat::WHITE . Language::translate("enter-center-area") . " " . $area->getName();
		}
		if( $msg != ''){
			$this->areaMessage( $msg, $player );
		}
        
		$playerName = strtolower( $player->getName() );
		$this->inArea[$playerName][] = strtolower( $area->getName() )."center";
		$this->runAreaEvent($area, $ev, "center");
		return;
	}

	/** Area event leave center
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @return false
	 */
	public function leaveAreaCenter(Area $area, PlayerMoveEvent $ev): void{
		// leaving area center
		$player = $ev->getPlayer();
		$playerName = strtolower( $player->getName() );
		$msg = '';
		if( !$area->getFlag("msg")  || $this->msgOpDsp( $area, $player ) ){
			$msg = TextFormat::WHITE . Language::translate("leaving-center-area"). " " . $area->getName();
		}
		if( $msg != ''){
			$this->areaMessage( $msg, $player );
		}
		if (($key = array_search( strtolower( $area->getName() )."center", $this->inArea[$playerName])) !== false) {
			unset($this->inArea[$playerName][$key]);
		}
		return;
	}

	/** Run Area Event
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @param string $eventtype
	 * @return false
	 */
	public function runAreaEvent(Area $area, PlayerMoveEvent $event, string $eventtype): void{
		$player = $event->getPlayer();
		$areaevents = $area->getEvents();
        $position = $player->getPosition();
        $playername = strtolower($player->getName());

        $runcmd = true;
        $c = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["CMDmode"] : $this->cmdmode);
        if( $c ){
            $runcmd = false; // flag default
        }
        if( $area->getFlag("cmdmode")  ){
			$runcmd = false;
		}

        if( $runcmd || $player->isOp() ){

            if( isset( $areaevents[$eventtype] ) && $areaevents[$eventtype] != '' ){
                $cmds = explode( "," , $areaevents[$eventtype] );
                if(count($cmds) > 0){
                    foreach($cmds as $cid){
                        if($cid != ''){

                            // check {player} or @p (and other stuff)
                            $command = $this->commandStringFilter( $area->commands[$cid], $event );

                            if ( !$player->isOp() && $this->useOpPerms($player, $area)  ) { // perm flag v1.0.4-11
                                $player->setOp(true);
                                $player->getServer()->dispatchCommand($player, $command);
                                $player->setOp(false);
                            }else{
                                if ( !$player->isOp() ){
                                    $this->getServer()->getPluginManager()->callEvent($ne = new PlayerCommandPreprocessEvent($player, "/" . $command));
                                    if(!$ne->isCancelled()) return; // don't do this (return) if player does not have permission
                                }
                                $player->getServer()->dispatchCommand($player, $command);
                            }

                        }
                    }
                }
            }

        }
	} 
	
	/** Command string filter
	 * @param str $command
	 * @param PlayerMoveEvent $event
	 * @return $command str
	 */
	public function commandStringFilter( $command, $event ){
        $playername =  $event->getPlayer()->getName();
		if( strpos( $command, "{player}" ) !== false ) {
        	$command = str_replace("{player}", $playername, $command); // replaces {player} with the player name
		}else if( strpos( $command, "@p" ) !== false ) { // only if {player} is not used - untill we know why @p does not work 
            $command = str_replace("@p", $playername, $command); // replaces @p with the player name 
		}
		return $command;
	}

	/** skippTime
	 * delay function for str player $nm repeating int $sec
	 * @param string $sec
	  * @return false
	 */
    public function skippTime($sec, $nm){
		$t = false;
        if(!isset($this->skipsec[$nm])){
            $this->skipsec[$nm] = time();  
        }else{
            if( ( ( time() - $sec ) > $this->skipsec[$nm]) || !$this->skipsec[$nm] ){
                $this->skipsec[$nm] = time();
                $t = true;  
            }
        }
		return $t;
	} 

	/** AreaMessage
	* define message type
	 * @param string $msg
	 * @param PlayerMoveEvent $ev->getPLayer()
	 * @param array $options
	 * @return true function
	 */
	public function areaMessage( $msg , $player ){
        if($this->options['Msgtype'] == 'msg'){
            $player->sendMessage($msg);
        }else if( $this->options['Msgtype'] == 'title'){
            $player->addTitle($msg);
            // $player->addTitle("Title", "Subtitle", $fadeIn = 20, $duration = 60, $fadeOut = 20);
        }else if($this->options['Msgtype'] == 'tip'){
			$player->sendTip($msg);
		}else{
			$player->sendPopup($msg);
		}
	}

	/**
	 * OpMsg define message persistent display
	 * @param Area $area
	 * @param PlayerMoveEvent $ev->getPLayer()
	 * @param array $options
	 * @return bool
	 */
	public function msgOpDsp( $area, $player ){
		if( isset( $this->options['Msgdisplay'] ) && $player->isOp() ){
			if( $this->options['Msgdisplay'] == 'on' ){
				return true;
			}else if( $this->options['Msgdisplay'] == 'op' && $area->isWhitelisted(strtolower($player->getName())) ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

    /** areaSounds
	 * @param array $sounds
	 */
	public function areaEventSound( $player ){
		//$player->addSound(new AnvilBreakSound($player));
		/** Todo:
		 * 1. add sounds
		 * 2. sound flag, config & command
		 * 3. add config different sounds & specification
		 */
	}

    /** List all area's
     * return
     */
    public function listAllAreas(){
        if( count($this->areas) > 0 ){
            $t = 'Area names: ';
            foreach($this->areas as $area){
                if( !empty( $area->getName() ) ){
                    $t .= $area->getName().', ';
                }
            }
            return rtrim($t,',');
        }else{
            return Language::translate("area-no-area-available");
        }
    }

	/** List Area Info
	 * @var obj area
	 */
	public function areaInfoDisplayList( $area ){

		$l = TextFormat::GRAY . " " .  Language::translate("area") . " " . TextFormat::AQUA . $area->getName();
        // Players in area
        $ap = [];
        foreach( $this->inArea as $p => $playerAreas ){
            if( $this->getServer()->getPlayer($p) ){
                foreach( $playerAreas as $a ){
                    if( $a == strtolower( $area->getName() ) ){
                        $ap[] = $p;
                    }
                }
            }else{
                unset( $this->inArea[$p] ); // remove player from inArea list
            }
        }
        if(count($ap) > 0 ){
            $l .=  "\n". TextFormat::GRAY . "  - ". Language::translate("players-in-area") .": \n " . TextFormat::GOLD . implode(", ", $ap );
        }
        
        // Area Flag
		$flgs = $area->getFlags(); 
		$l .= "\n". TextFormat::GRAY . "  - ". Language::translate("flags")  ." :";
		foreach($flgs as $fi => $flg){
			$l .= "\n". TextFormat::GOLD . "    ". $fi . ": ";
			if( $flg ){
				$l .= TextFormat::GREEN . Language::translate("status-on");
			}else{
				$l .= TextFormat::RED . Language::translate("status-off");
			}
		}

		// Area Commands by event
		if( $cmds = $area->getCommands() && count( $area->getCommands() ) > 0 ){
			$l.= "\n". TextFormat::GRAY . "  - ".Language::translate("cmds").":";
			foreach( $area->getEvents() as $type => $list ){
				$ids = explode(",",$list);
				$l .= "\n". TextFormat::GOLD . "    On ". $type;
				foreach($ids as $cmdid){
					if( isset($area->commands[$cmdid]) ){
						$l .= "\n". TextFormat::GREEN . "    ". $cmdid . ": ".$area->commands[$cmdid];
					}
				}
			}
		}else{
			$l .=  TextFormat::GRAY . "\n  - ". Language::translate("area-no-commands");
		}
		$l .=  "\n". TextFormat::GRAY . "  - ".Language::translate("area-whitelist").": " . TextFormat::WHITE . implode(", ", $area->getWhitelist()) . "\n";
		return $l;

	}

    /** isWhitelisted global or specific area
	 * @param Player $player
	 * @param Area $area
     */
    public function isWhitelisted( $player, $area = false ): bool{

        if( $area instanceof Area && $area->isWhitelisted( strtolower( $player->getName() ) ) && $area->getLevel() == $player->getPosition()->getLevel() ){
           return true;
        }else{
            foreach($this->areas as $area){
                if( $area->isWhitelisted( strtolower( $player->getName() ) ) && $area->getLevel() == $player->getPosition()->getLevel() ){
                    return true;
                }
            }
        }
        return false;
    }

    /** Check Floating Area Title placement( FloatingTextParticle )
	 * @param Vector3 $pos
	 * @param string  $text
	 * @param string  $title
	 */
    public function checkAreaTitles( $player, $level ) : void{
        foreach($this->areas as $area){
            $this->hideAreaTitle( $player, $level, $area );
            if( $level->getName() == $area->getLevelName() &&
               (( $this->options["Areadisplay"] == 'on' && ( !$area->getFlag("msg") || $area->isWhitelisted( strtolower( $player->getName() ) ) ) ) ||
                ( $this->options["Areadisplay"] == 'op' && ( $player->isOp() || $area->isWhitelisted( strtolower( $player->getName() ) ) )
                ))){
                $this->placeAreaTitle( $player, $level, $area );
            }
        }
		return;
    }

     /** Set Floating Area Title ( FloatingTextParticle )
	 * @param Player $player
     * @param Level $level
	 * @param Area  $area
	 */
    public function placeAreaTitle( $player, $level, $area ) : void{

        if( isset($this->areaTitles[strtolower($player->getName())][ $area->getName() ]) ){
            // activate particle
            $this->areaTitles[strtolower($player->getName())][ $area->getName() ]->setInvisible(false);
        }else{
            $cx = $area->getSecondPosition()->getFloorX() + ( ( $area->getFirstPosition()->getFloorX() - $area->getSecondPosition()->getFloorX() ) / 2 );
            $cz = $area->getSecondPosition()->getFloorZ() + ( ( $area->getFirstPosition()->getFloorZ() - $area->getSecondPosition()->getFloorZ() ) / 2 );
            $cy = max( $area->getSecondPosition()->getFloorY(), $area->getFirstPosition()->getFloorY()) - 2;
            // area set title pos
            $this->areaTitles[strtolower($player->getName())][ $area->getName() ] = new FloatingTextParticle( new Position($cx, $cy, $cz, $area->getLevel() ), "",  TextFormat::AQUA . $area->getName() );
        }
        $level->addParticle( $this->areaTitles[strtolower($player->getName())][ $area->getName() ], [$player]);
		return;
    }

     /** Hide Floating Area Title ( FloatingTextParticle )
	 * @param Player $player
     * @param Level $level
	 * @param Area  $area
	 */
    public function hideAreaTitle( $player, $level, $area ) : void{

        if( isset($this->areaTitles[strtolower($player->getName())][ $area->getName() ]) ){
            // hide particle
            $this->areaTitles[strtolower($player->getName())][ $area->getName() ]->setInvisible(true);
            $level->addParticle( $this->areaTitles[strtolower($player->getName())][ $area->getName() ], [$player]);
        }
		return;
    }

	/** Save areas
	 * @var obj area
	 * @file areas.json
	 */
	public function saveAreas() : void{
		$areas = [];
		foreach($this->areas as $area){
			$areas[] = ["name" => $area->getName(), "desc" => $area->getDesc(), "flags" => $area->getFlags(), "pos1" => [$area->getFirstPosition()->getFloorX(), $area->getFirstPosition()->getFloorY(), $area->getFirstPosition()->getFloorZ()] , "pos2" => [$area->getSecondPosition()->getFloorX(), $area->getSecondPosition()->getFloorY(), $area->getSecondPosition()->getFloorZ()], "level" => $area->getLevelName(), "whitelist" => $area->getWhitelist(), "commands" => $area->getCommands(), "events" => $area->getEvents()];

            $this->areaList[strtolower( $area->getName() )] = $area; // name associated area list for inArea check
		}
		file_put_contents($this->getDataFolder() . "areas.json", json_encode($areas));
	}

    /**  Festival Console Sign Flag for developers
     *   makes it easy to find Festival console output fast
     */
    public function codeSigned(){
        $this->getLogger()->info( "Made by Genboy" );
    }

}
