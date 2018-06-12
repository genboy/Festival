<?php declare(strict_types = 1);

/** src/genboy/Festival/Main.php */
namespace genboy\Festival;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener{

	/** @var array[] */
	private $levels = [];
	/** @var Area[] */
	public $areas = [];
	/** @var array[] */
	public $flagset = [];
	/** @var array[] */
	public $options = [];

	/** @var bool */
	private $god = false;
	/** @var bool */
	private $pvp = false;
	/** @var bool */
	private $flight = false;
	/** @var bool */
	private $edit = false;
	/** @var bool */
	private $touch = false;
	/** @var bool */
	private $effects = false;
	/** @var bool */
	private $msg = false;
	/** @var bool */
	private $passage = false;
	/** @var bool */
	private $perms = false;
	/** @var bool */
	private $drop = false;

	/** @var bool[] */
	private $selectingFirst = [];
	/** @var bool[] */
	private $selectingSecond = [];

	/** @var Vector3[] */
	private $firstPosition = [];
	/** @var Vector3[] */
	private $secondPosition = [];

	/** @var string[] */
	private $inArea = [];

	/** @var array[] */
	private $skipsec = [];

	/** @var string[] */
	public $playerTP = [];

	/** Enable
	 * @return $this
	 */
	public function onEnable() : void{

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

        $newchange = []; // check missing flags or options in config

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

		$data = json_decode(file_get_contents($this->getDataFolder() . "areas.json"), true);


		foreach($data as $datum){
            
			$flags = $datum["flags"];
			if( isset($datum["flags"]["barrier"]) ){
				$flags["passage"] = $datum["flags"]["barrier"];
				unset($flags["barrier"]);
				$newchange['Passage'] = "! Old Barrier config was used, now set to 'false'; please rename 'Barrier' to 'Passage' in config.yml";
			}
            
			if( !isset($datum["flags"]["perms"]) ){
				$flags["perms"] = false;
				$newchange['Perms'] = "! Area Perms flag missing, now updated to 'false';  please see /resources/config.yml";
			}
			if( !isset($datum["flags"]["drop"]) ){
				$flags["drop"] = false;
				$newchange['Drop'] = "! Area Drop flag missing, now updated to 'false'; please see /resources/config.yml";
			}
            //new flags v 1.0.5-12
			if( !isset($datum["flags"]["effects"]) ){
				$flags["effects"] = false;
				$newchange['Effects'] = "! Area Effects flag missing, now updated to 'false'; please see /resources/config.yml";
			}
            //new flags v 1.0.6-13
			if( !isset($datum["flags"]["pvp"]) ){
				$flags["pvp"] = false;
				$newchange['PVP'] = "! Area PVP flag missing, now updated to 'false'; please see /resources/config.yml";
			}
			if( !isset($datum["flags"]["flight"]) ){
				$flags["flight"] = false;
				$newchange['Flight'] = "! Area Flight flag missing, now updated to 'false'; please see /resources/config.yml";
			}

			new Area($datum["name"], $datum["desc"], $flags, new Vector3($datum["pos1"]["0"], $datum["pos1"]["1"], $datum["pos1"]["2"]), new Vector3($datum["pos2"]["0"], $datum["pos2"]["1"], $datum["pos2"]["2"]), $datum["level"], $datum["whitelist"], $datum["commands"], $datum["events"], $this);
		}

		$c = yaml_parse_file($this->getDataFolder() . "config.yml");
		
		/* Config updating Code */
		if( isset( $c["Options"] ) && is_array( $c["Options"] ) ){

			if(!isset($c["Options"]["Msgtype"])){
				$c["Options"]["Msgtype"] = 'pop';
				$newchange['Msgtype'] = "! Msgtype option missing in config.yml, now set to 'pop'; please see /resources/config.yml";
			}
			if(!isset($c["Options"]["Msgdisplay"])){
				$c["Options"]["Msgdisplay"] = 'off';
				$newchange['Msgdisplay'] = "! Msgdisplay option missing in config.yml, now set to 'off'; please see /resources/config.yml";
			} //.. 1.0.3-11+ options check

            // check since v1.0.5-12
            if(!isset($c["Options"]["AutoWhitelist"])){
				$c["Options"]["AutoWhitelist"] = 'on';
				$newchange['AutoWhitelist'] = "! AutoWhitelist option missing in config.yml, now set to 'on'; please see /resources/config.yml";
			} //.. new options check
            
			$this->options = $c["Options"];

		}else{
			$this->options = array("Msgtype"=>"pop", "Msgdisplay"=>"off", "AutoWhitelist"=>"on"); // Fallback defaults
            $newchange['Options'] = "! Config Options missing in config.yml, defautls are set for now; please see /resources/config.yml";
		}

        /**
         * config default check and overwrite plugin defaults
         */
		if(!isset($c["Default"]["God"])) {
			$c["Default"]["God"] = false;
		}
		if(!isset($c["Default"]["Edit"])) {
			$c["Default"]["Edit"] = true;
		}
		if(!isset($c["Default"]["Touch"])) {
			$c["Default"]["Touch"] = false;
		}
		if(!isset($c["Default"]["Msg"])) {
			$c["Default"]["Msg"] = false;
		}
		if( isset($c["Default"]["Barrier"]) ){ // remove in v1.0.5-11
			$c["Default"]["Passage"] =  $c["Default"]["Barrier"];
		}else if(!isset($c["Default"]["Passage"])) {
			$c["Default"]["Passage"] = false;
		}
		// new in v1.0.4-11
		if(!isset($c["Default"]["Perms"])) {
			$c["Default"]["Perms"] = false;
		}
		if(!isset($c["Default"]["Drop"])) {
			$c["Default"]["Drop"] = false;
		}
		// new in v1.0.5-12
		if(!isset($c["Default"]["Effects"])) {
			$c["Default"]["Effects"] = false;
		}
		// new in v1.0.6-13
		if(!isset($c["Default"]["PVP"])) {
			$c["Default"]["PVP"] = false;
		}
		if(!isset($c["Default"]["Flight"])) {
			$c["Default"]["Flight"] = false;
		}

		$this->god = $c["Default"]["God"];
		$this->edit = $c["Default"]["Edit"];
		$this->touch = $c["Default"]["Touch"];
		$this->msg = $c["Default"]["Msg"];
		// changed in v1.0.3-11
		$this->passage = $c["Default"]["Passage"];
		// new in v1.0.4-11
		$this->perms = $c["Default"]["Perms"];
		$this->drop = $c["Default"]["Drop"];
        // new in v1.0.5-12
		$this->effects = $c["Default"]["Effects"];
        $this->flagset = $c['Default']; 
		// new in v1.0.6-13
		$this->pvp = $c["Default"]["PVP"];
		$this->flight = $c["Default"]["Flight"];
        
        // world default flag settings
		if(is_array( $c["Worlds"] )){
			foreach($c["Worlds"] as $level => $flags){

				// check since v1.0.3-11
				if( isset($flags["Barrier"]) ){
					$flags["Passage"] = $flags["Barrier"];
					unset($flags["Barrier"]);
				}
				if( !isset($flags["Passage"]) ){
					$flags["Passage"] = $this->passage;
				}

				// new v1.0.4-11
				if( !isset($flags["Perms"]) ){
					$flags["Perms"] = $this->perms;
				}
				if( !isset($flags["Drop"]) ){
					$flags["Drop"] = $this->drop;
				}
                // new v1.0.5-12
				if( !isset($flags["Effects"]) ){
					$flags["Effects"] = $this->effects;
				}

                // new v1.0.6-13
				if( !isset($flags["PVP"]) ){
					$flags["PVP"] = $this->pvp;
				}
				if( !isset($flags["Flight"]) ){
					$flags["Flight"] = $this->flight;
				}

				$this->levels[$level] = $flags;
			}
		}

		// all save :)
		$this->saveAreas();

		// console output
        $this->codeSigned();

		$ca = 0;
		foreach( $this->areas as $a ){
			$ca = $ca + count( $a->getCommands() );
		}
		$this->getLogger()->info( "  ". $ca ." commands in " . count($this->areas) . " areas" );
		
        //v1.0.6-13-dev
		if( count($newchange) > 0 ){
            foreach($newchange as $ttl => $txt){
			     $this->getLogger()->info( $ttl . ": " . $txt );
            }
		}
	}

    /** Flag check experimental (synonym to original name)
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
            "effects","magic","effect",
            "drop",
            "msg","message",
            "passage","pass","barrier",
            "perms","perm"
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
            if( $str == "interact" ){
                $flag = "touch";
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
            if( $str == "effect" || $str == "effects" ){
                $flag = "effects";
            }
        }
        return $flag;
    }

	/** Commands
	 * @param CommandSender $sender
	 * @param Command $cmd
	 * @param string $label 
	 * @param array $args
	 * @return bool 
	 */
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		if(!($sender instanceof Player)){
			$sender->sendMessage(TextFormat::RED . "Command must be used in-game.");  
			return true;
		}
		if(!isset($args[0])){
			return false;
		}
		$playerName = strtolower($sender->getName());
		$action = strtolower($args[0]);
		$o = "";
		switch($action){
			case "pos1":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.pos1")){
					if(isset($this->selectingFirst[$playerName]) || isset($this->selectingSecond[$playerName])){
						$o = TextFormat::RED . "You're already selecting a position!";
					}else{
						$this->selectingFirst[$playerName] = true;
						$o = TextFormat::GREEN . "Please place or break the first position.";
					}
				}else{
					$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
			break;
			case "pos2":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.pos2")){
					if(isset($this->selectingFirst[$playerName]) || isset($this->selectingSecond[$playerName])){
						$o = TextFormat::RED . "You're already selecting a position!";
					}else{
						$this->selectingSecond[$playerName] = true;
						$o = TextFormat::GREEN . "Please place or break the second position.";
					}
				}else{
					$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
			break;
			case "create":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.area") || $sender->hasPermission("festival.command.fe.create")){
					if(isset($args[1])){
						if(isset($this->firstPosition[$playerName], $this->secondPosition[$playerName])){
							if(!isset($this->areas[strtolower($args[1])])){
                                // get level default flags
                                $flags = $this->flagset;
                                if( isset($this->levels[$sender->getLevel()->getName()]) ){
                                    if( is_array( $this->levels[$sender->getLevel()->getName()] ) ){
                                        $flags = $this->levels[$sender->getLevel()->getName()];
                                    }
                                }
                                // get default whitelisting
                                $whitelist = []; 
                                if( $this->options["AutoWhitelist"] == "on" ){ 
                                    $whitelist = [$playerName];
                                }
                                  
                                new Area(
                                    strtolower($args[1]),
                                    "",
                                    ["edit" => $flags['Edit'], "god" => $flags['God'], "pvp" => $flags["PVP"], "flight"=> $flags["Flight"], "touch" => $flags['Touch'], "effects" => $flags['Effects'], "drop" => $flags['Drop'], "msg" => $flags['Msg'], "passage" => $flags['Passage'], "perms" => $flags['Perms']],
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
								$o = TextFormat::AQUA . "Area created!";
							}else{
								$o = TextFormat::RED . "An area with that name already exists.";
							}
						}else{
							$o = TextFormat::RED . "Please select both positions first.";
						}
					}else{
						$o = TextFormat::RED . "Please specify a name for this area.";
					}
				}else{
					$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
			break;
			case "desc":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.desc")){
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
								$o = TextFormat::GREEN . "Area ". TextFormat::LIGHT_PURPLE . $area->getName() . TextFormat::GREEN . " description saved";
							}else{
								$o = TextFormat::RED . "Please write the description. Usage /fe desc <areaname> <..>";
							}
						}else{
							$o = TextFormat::RED . "Area does not exist.";
						}
					}else{
						$o = TextFormat::RED . "Please specify an area to edit the description. Usage: /fe desc <areaname> <desc>";
					}
				}else{  
					$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
			break;
			case "list":
				if( $sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.list")){


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

								    $t .= $this->areaInfoList( $area );

								    $i++;

								}

							}

						}

						if( $i > 0 ){

							$o .= TextFormat::DARK_PURPLE ."---- Area list ----\n";

							$o .= TextFormat::GRAY . "level " . TextFormat::YELLOW . $lvl->getName() .":\n". $t;

						}

					}

					if($o != ''){

						$o .= TextFormat::DARK_PURPLE ."----------------\n";

					}



					if($o == ''){

						$o = "There are no areas that you can edit";

					}

				}
            break;
			case "here":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.here")){
					$o = "";
					foreach($this->areas as $area){

						if($area->contains($sender->getPosition(), $sender->getLevel()->getName()) && $area->getWhitelist() !== null){
							$o .= TextFormat::DARK_PURPLE ."---- Area here ----\n";
							$o .= $this->areaInfoList( $area );
							$o .= TextFormat::DARK_PURPLE ."----------------\n";
						}
					}
					if($o === "") {
						$o = TextFormat::RED . "You are in an unknown area";
					}
				}
			break;
			case "tp":
				if (!isset($args[1])){
					$o = TextFormat::RED . "You must specify an existing Area name";
					break;
				}
                if( isset( $this->areas[strtolower($args[1])] ) ){

                    $area = $this->areas[strtolower($args[1])];

                    $position = $sender->getPosition();

                    $perms = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[ $position->getLevel()->getName() ]["Perms"] : $this->perms);

                    if( $perms || $area->isWhitelisted($playerName) || $sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.tp")){

                        $levelName = $area->getLevelName();
                        if(isset($levelName) && Server::getInstance()->loadLevel($levelName) != false){
                                $o = TextFormat::GREEN . "You are teleporting to Area " . $args[1];
                                $cx = $area->getSecondPosition()->getX() + ( ( $area->getFirstPosition()->getX() - $area->getSecondPosition()->getX() ) / 2 );
                                $cz = $area->getSecondPosition()->getZ() + ( ( $area->getFirstPosition()->getZ() - $area->getSecondPosition()->getZ() ) / 2 );
                                $cy1 = min( $area->getSecondPosition()->getY(), $area->getFirstPosition()->getY());
                                $cy2 = max( $area->getSecondPosition()->getY(), $area->getFirstPosition()->getY());
                                $this->playerTP[$playerName] = true; // player tp active
                                //$this->areaMessage( 'Fall save on!', $sender );
                                $sender->sendMessage( $playerName );
                                $sender->teleport( new Position( $cx, $cy2+ 0.5, $cz, $area->getLevel() ) );

                        }else{
                            $o = TextFormat::RED . "The level " . $levelName . " for Area ". $args[1] ." cannot be found";
                        }
                    }else{
                        $o = TextFormat::RED . "You do not have permission to use this subcommand.";
                    }
                }else{
                    $list = $this->listAllAreas();
                    $o = TextFormat::RED . "The Area " . $args[1] . " could not be found. ". $list;
                }
			break;
			case "f":
			case "flag":
			case "touch":
			case "pvp":
			case "flight":
			case "fly":
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
			case "drop":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.flag")){
					if(isset($args[1])){
                        
						/**
						* Revert a flag in all area's (v1.0.4-11)
						*/
						if($args[1] == 'swappall'){
                            $flag = $this->isFlag( $args[0] ); // v1.0.6-13
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
								$o = TextFormat::GREEN . "Flag " . $flag . " set to " . $status . " for area " . $area->getName() . "!";
                                
							}else{
                                
                                
								// excute long (old) notation
								if(isset($args[2])){
                                    
                                    if( $args[2] == "list" ){
                                        
                                        //$o = TextFormat::RED . "Flag list in develoment";
                                        $flgs = $area->getFlags(); 
                                        $l = $area->getName() . TextFormat::GRAY . " flags:";
                                        foreach($flgs as $fi => $flg){
                                            $l .= "\n". TextFormat::GOLD . "    ". $fi . ": ";
                                            if( $flg ){
                                                $l .= TextFormat::GREEN . "on";
                                            }else{
                                                $l .= TextFormat::RED . "off";
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
										$o = TextFormat::GREEN . "Flag " . $flag . " set to " . $status . " for area " . $area->getName() . "!";
									}else{
										$o = TextFormat::RED . "Flag not found. (Flags: edit, god, pvp, flight, touch, effects, msg, passage, perms, drop)";
									}
								}else{
									$o = TextFormat::RED . "Please specify a flag. (Flags: edit, god, pvp, flight, touch, effects, msg, passage, perms, drop)";
								}
							}
						}else{
							$o = TextFormat::RED . "Area doesn't exist.";
						}
					}else{
						$o = TextFormat::RED . "Please specify the area you would like to flag.";
					}
				}else{
					$o = TextFormat::RED . "You do not have permission to use this subcommand."; 
				}
			break;
			case "del":
			case "delete":
			case "remove":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.delete")){
					if(isset($args[1])){
						if(isset($this->areas[strtolower($args[1])])){
							$area = $this->areas[strtolower($args[1])];
							$area->delete();
							$o = TextFormat::GREEN . "Area deleted!";
						}else{
							$o = TextFormat::RED . "Area does not exist.";
						}
					}else{
						$o = TextFormat::RED . "Please specify an area to delete.";
					}
				}else{
					$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
			break;
			case "whitelist":
				if($sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.whitelist")){
					if(isset($args[1], $this->areas[strtolower($args[1])])){
						$area = $this->areas[strtolower($args[1])];
						if(isset($args[2])){
							$action = strtolower($args[2]);
							switch($action){
								case "add":
								$w = ($this->getServer()->getPlayer($args[3]) instanceof Player ? strtolower($this->getServer()->getPlayer($args[3])->getName()) : strtolower($args[3]));
								if(!$area->isWhitelisted($w)){
									$area->setWhitelisted($w);
									$o = TextFormat::GREEN . "Player $w has been whitelisted in area " . $area->getName() . ".";
								}else{
									$o = TextFormat::RED . "Player $w is already whitelisted in area " . $area->getName() . ".";
								}
								break;
								case "list":
								$o = TextFormat::AQUA . "Area " . $area->getName() . "'s whitelist:" . TextFormat::RESET;
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
									$o = TextFormat::GREEN . "Player $w has been unwhitelisted in area " . $area->getName() . ".";
								}else{
									$o = TextFormat::RED . "Player $w is already unwhitelisted in area " . $area->getName() . ".";
								}
								break;
								default:
								$o = TextFormat::RED . "Please specify a valid action. Usage: /area whitelist " . $area->getName() . " <add/list/remove> [player]";
								break;
							}
						}else{
							$o = TextFormat::RED . "Please specify an action. Usage: /area whitelist " . $area->getName() . " <add/list/remove> [player]";
						}
					}else{
						$o = TextFormat::RED . "Area doesn't exist. Usage: /area whitelist <area> <add/list/remove> [player]";
					}
				}else{
					$o = TextFormat::RED . "You do not have permission to use this subcommand.";
				}
			break;
			case "c":
			case "cmd":
			case "command": /** /fe command <areaname> <add|list|edit|del> <commandindex> <commandstring>  */
				if( isset($args[1]) && (  $sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fe") || $sender->hasPermission("festival.command.fe.command") ) ){
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
													$o = TextFormat::RED .'Command id:'.$cid.' allready set for area '.$do.'-event.';
												}else{
													$eventarr[] = $cid;
													$eventstr = implode(",", $eventarr );
													$area->events[$do] = $eventstr;
													$o = TextFormat::RED .'Command id:'.$cid.' set for area '.$do.'-event';
												}
											}else{
												$area->events[$do] = $cid;
												$o = TextFormat::RED .'Command id:'.$cid.' set for area '.$do.'-event';
											}
											$area->commands[$cid] = $commandstring;
											$this->saveAreas();
											$o = TextFormat::GREEN .'Command (id:'.$cid.') added to area '.$ar;
										}else{
											$o = TextFormat::RED .'Command id:'.$cid.' allready used for '.$ar.', edit this id or use another id.';
										}
									}else{
										$o = TextFormat::RED .'Please specify the command ID and command string to add. Usage: /fe command <areaname> add <COMMANDID> <COMMANDSTRING>';
									}
								break;
								case "list":
									$ar = $this->areas[strtolower($args[1])];
									if( isset($ar->commands) ){
										$o = TextFormat::WHITE . $args[1] . TextFormat::AQUA .' command list:';
										foreach($ar->events as $type => $list){
											if( trim($list,",") != "" ){
												$o .= "\n". TextFormat::YELLOW ."On ". $type . ":";
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
									//$o = '/fe command <eventname> event <COMMANDID> <EVENTTYPE>';
									if( isset($args[3]) && isset($args[4]) ){
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
												$o = TextFormat::GREEN .'Command (id:'.$cid.') event is now '.$evt;
											}else{
												$o = TextFormat::RED .'Command (id:'.$cid.') event '.$evt.' change failed';
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
											$o = TextFormat::GREEN .'Command (id:'.$cid.') edited';
										}else{
											$o = TextFormat::RED .'Command id:'.$cid.' could not be found. Check the command id with /fe command <areaname> list';
										}
									}else{
										$o = TextFormat::RED .'Please specify the command ID and command string to add. Usage: /fe command <areaname> add <COMMANDID> <COMMANDSTRING>';
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
											$o = TextFormat::RED .'Command ID not found. See the commands with /fe event command <areaname> list';
										}
									}else{
										$o = TextFormat::RED .'Please specify the command ID to delete. Usage /fe event command <areaname> del <COMMANDID>';
									}
							break;
							default:
								return false;
							}
						}else{
							$o = TextFormat::RED . "Please add an action to perform with command.  Usage: /fe command <areaname> <add/list/edit/del> <commandID> <commandstring>.";
						}
					}else{
						$o = TextFormat::RED . "Area not found, please submit a valid name. Usage: /fe command <areaname> <add/list/edit/del> <commandID> <commandstring>.";
					}
				}else{
					if(!isset($args[1])){
						$o = TextFormat::RED . "Area not found, please submit a valid name. Usage: /fe command <areaname> <add/list/edit/del> <commandID> <commandstring>.";
					}else{
						$o = TextFormat::RED . "You do not have permission to use this subcommand.";
					}
				}
			break;
			default:
				return false;
		}
		$sender->sendMessage($o);
		return true;
	}

    /** on quit
	 * @param Event $event
	 * @return bool
	 */
    public function onQuit(PlayerQuitEvent $event){

        $playerName = strtolower($event->getPlayer()->getName());
        $lvl = $event->getPlayer()->getLevel()->getName();
        unset($this->inArea[$playerName]);

    }

	/** Hurt
	 * @param Entity $entity
	 * @return bool
	 */
	public function canGetHurt(Entity $entity) : bool{
		$o = true;
		$default = (isset($this->levels[$entity->getLevel()->getName()]) ? $this->levels[$entity->getLevel()->getName()]["God"] : $this->god);
		if($default){
			$o = false; 
		}
		foreach($this->areas as $area){
            
			if($area->contains(new Vector3($entity->getX(), $entity->getY(), $entity->getZ()), $entity->getLevel()->getName())){
                
				if($default && !$area->getFlag("god")){
					$o = true; 
					break;
				}             
				if($area->getFlag("god")){
					$o = false; 
				}
                
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
                $default = (isset($this->levels[$entity->getLevel()->getName()]) ? $this->levels[$entity->getLevel()->getName()]["PVP"] : $this->pvp);
                if($default){
                    $o = false;
                }
                foreach($this->areas as $area){
                    if($area->contains(new Vector3($entity->getX(), $entity->getY(), $entity->getZ()), $entity->getLevel()->getName())){
                        $god = $area->getFlag("god");
                        if($default && !$area->getFlag("pvp")){
                            $o = true;
                            break;
                        }
                        if($area->getFlag("pvp")){
                            $o = false;
                            break;
                        }
                    }
                }
            }
        }
        if( !$o ){
            $player = $ev->getDamager();
            if( $this->skippTime( 2, strtolower($player->getName()) ) ){
                if( $god ){
                    $this->areaMessage( 'All players are God in this Area!', $player );
                }else{
                    $this->areaMessage( 'You are in a No-PVP Area!', $player );
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
			if(!$this->canGetHurt($player)){
				$ev->setCancelled();
                return false;
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

	/** On hurt
	 * @param EntityDamageEvent $event
	 * @ignoreCancelled true
	 */
	public function onHurt(EntityDamageEvent $event) : void{
		$this->canDamage( $event );
	}

	/** On Damage
	 * @param EntityDamageEvent $event
	 * @ignoreCancelled true
	 */
	public function onDamage(EntityDamageEvent $event) : void{
		$this->canDamage( $event );
	}

	/** Edit
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
	 */
	public function canEdit(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
		$o = true;
		$g = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Edit"] : $this->edit);
		if($g){
			$o = false;
		}
		foreach($this->areas as $area){
			if($area->contains($position, $position->getLevel()->getName())){
				if($area->getFlag("edit")){
					$o = false;
				}
				if($area->isWhitelisted(strtolower($player->getName()))){
					$o = true;
					break;
				}
				if(!$area->getFlag("edit") && $g){
					$o = true;
					break;
				}
			}
		}
		return $o;
	}

	/** Touch
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
	 */
	public function canTouch(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
		$o = true;
		$default = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Touch"] : $this->touch);
		if($default){
			$o = false;
		}
		foreach($this->areas as $area){
			if($area->contains(new Vector3($position->getX(), $position->getY(), $position->getZ()), $position->getLevel()->getName())){
				if($area->getFlag("touch")){
					$o = false;
				}
				if($area->isWhitelisted(strtolower($player->getName()))){
					$o = true;
					break;
				}
				if(!$area->getFlag("touch") && $default){
					$o = true;
					break;
				}
			}
		}
		return $o;
	}

	/** Block Touch
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

	/** Item drop
	 * @param itemDropEvent $event
	 * @ignoreCancelled true
	 */
	public function onDrop(PlayerDropItemEvent $event)
	{
		$player = $event->getPlayer();
		$position = $player->getPosition();

		if(!$this->canDrop($player, $position)){
			$event->setCancelled();
			return;
		}
	}

	/** on Drop
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
	 */
	public function canDrop(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
		$o = true;
		$g = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Drop"] : $this->drop);
		if($g){
			$o = false;
		}
		foreach($this->areas as $area){
			if($area->contains($position, $position->getLevel()->getName())){
				if($area->getFlag("drop")){
					$o = false;
				}
				if($area->isWhitelisted(strtolower($player->getName()))){
					$o = true;
					break;
				}
				if(!$area->getFlag("drop") && $g){
					$o = true;
					break;
				}
			}
		}
		return $o;
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
			$player->sendMessage(TextFormat::GREEN . "Position 1 set to: (" . $block->getX() . ", " . $block->getY() . ", " . $block->getZ() . ")");
			$event->setCancelled();
		}elseif(isset($this->selectingSecond[$playerName])){
			unset($this->selectingSecond[$playerName]);
			$this->secondPosition[$playerName] = $block->asVector3();
			$player->sendMessage(TextFormat::GREEN . "Position 2 set to: (" . $block->getX() . ", " . $block->getY() . ", " . $block->getZ() . ")");
			$event->setCancelled();
		}else{
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
			$player->sendMessage(TextFormat::GREEN . "Position 1 set to: (" . $block->getX() . ", " . $block->getY() . ", " . $block->getZ() . ")");
			$event->setCancelled();
		}elseif(isset($this->selectingSecond[$playerName])){
			unset($this->selectingSecond[$playerName]);

			$this->secondPosition[$playerName] = $block->asVector3();
			$player->sendMessage(TextFormat::GREEN . "Position 2 set to: (" . $block->getX() . ", " . $block->getY() . ", " . $block->getZ() . ")");
			$event->setCancelled();
		}else{
			if(!$this->canEdit($player, $block)){
				$event->setCancelled();
			}
		}
	}

	/** Op Perms
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
		$g = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[ $position->getLevel()->getName() ]["Perms"] : $this->perms);
		if($g){
			$o = false;
		}
		if( $area->getFlag("perms") ){
			$o = false;
		}
		if( $area->isWhitelisted( strtolower( $player->getName() ) ) ){
			$o = true;
		}
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
		$o = true;
		$g = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Effects"] : $this->effects);
		if($g){
			$o = false;
		}
		foreach($this->areas as $area){
			if($area->contains($position, $position->getLevel()->getName())){
				if($area->getFlag("effects")){
					$o = false;
				}
				if($area->isWhitelisted(strtolower($player->getName()))){
					$o = true;
					break;
				}
				if(!$area->getFlag("effects") && $g){
					$o = true;
					break;
				}
			}
		}
		return $o;
	}



    /** Flight
	 * @param Player $player
     */
    public function checkPlayerFlying(Player $player){

        $fly = true;
		$position = $player->getPosition();
        $f = (isset($this->levels[$position->getLevel()->getName()]) ? $this->levels[$position->getLevel()->getName()]["Flight"] : $this->flight);
        if( $f ){
            $fly = false; // flag default
        }

        foreach($this->areas as $area){
            if( $area->contains( $player->getPosition(), $player->getLevel()->getName() ) ){
                if(  $area->getFlag("flight") && !$area->isWhitelisted( strtolower($player->getName())) ){
                    $fly = false; // flag area
                }else{
                    $fly = true;
                }
            }
        }
        if( $player->isOp() ){
            $fly = true; // ops can fly
        }

        $msg = '';
        if( !$fly && $player->isFlying() ){
            $this->playerTP[ strtolower( $player->getName() ) ] = true; // player tp active (fall save)
            $player->setFlying(false);
            $player->sendMessage(  TextFormat::RED . "NO Flying here!" );
        }
        if( $fly && !$player->isFlying() && !$player->getAllowFlight() ){
            $player->sendMessage( TextFormat::GREEN . "Flying allowed here!" );
        }
        $player->setAllowFlight($fly);

        return $fly;

    }


	/** On player move ..
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
			
            // Player area passage
            if( $area->getFlag("passage") ){
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
            
            /** Area Player Monitor */
            //$this->AreaPlayerMonitor($area, $ev);
            
		} 

        $this->checkPlayerFlying( $player );

		return;
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
			$msg = TextFormat::WHITE . $area->getName(). TextFormat::RED . " passage barrier detected!";
			$player->sendMessage( $msg );
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
				$msg = TextFormat::YELLOW . "You can not Enter area " . $area->getName();
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
				$msg = TextFormat::YELLOW . "You can not leave area " . $area->getName();
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
			$msg = TextFormat::AQUA . $player->getName() . " enter " . $area->getName();
			if( $area->getDesc() ){
				$msg .= "\n". TextFormat::WHITE . $area->getDesc();
			}
			if( $msg != ''){
				$this->areaMessage( $msg, $player );
			} 
		}
        
        // effects check
        if( $this->canUseEffects( $player ) ){
            // use effects
        }else{
            foreach ($player->getEffects() as $effect) {
                $player->removeEffect($effect->getId());
            }
        }
        
		$playerName = strtolower( $player->getName() );
		$this->inArea[$playerName][] = strtolower( $area->getName() );
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
			$msg .= TextFormat::YELLOW . $player->getName() . " leaving " . $area->getName();
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
			$msg = TextFormat::WHITE . "Enter the center of area " . $area->getName();
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
			$msg = TextFormat::WHITE . "Leaving the center of area " . $area->getName();
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
								if(!$ne->isCancelled()) return; // don't do this
							} 
							$player->getServer()->dispatchCommand($player, $command); 
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
		if($this->options['Msgtype'] == 'tip'){
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
            return 'No area available..';
        }
    }
	/** List Area Info
	 * @var obj area
	 */
	public function areaInfoList( $area ){

		$l = TextFormat::GRAY . "  area " . TextFormat::AQUA . $area->getName();
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
            $l .=  "\n". TextFormat::GRAY . "  - players in area: \n    " . TextFormat::GOLD . implode(", ", $ap );
        }
        
        // Area Flag text colors GREEN, AQUA, BLUE, RED, WHITE, YELLOW, LIGHT_PURPLE, DARK_PURPLE, GOLD, GRAY
		$flgs = $area->getFlags(); 
		$l .= "\n". TextFormat::GRAY . "  - flags:";
		foreach($flgs as $fi => $flg){
			$l .= "\n". TextFormat::GOLD . "    ". $fi . ": ";
			if( $flg ){
				$l .= TextFormat::GREEN . "on";
			}else{
				$l .= TextFormat::RED . "off";
			}
		} 

		// Area Commands by event
		if( $cmds = $area->getCommands() && count( $area->getCommands() ) > 0 ){
			$l.= "\n". TextFormat::GRAY . "  - commands:";
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
			$l .=  TextFormat::GRAY . "\n  - no commands attachted";
		}
		$l .=  "\n". TextFormat::GRAY . "  - whitelist: " . TextFormat::WHITE . implode(", ", $area->getWhitelist()) . "\n";
		return $l;

	}


	/** Save areas
	 * @var obj area
	 * @file areas.json
	 */
	public function saveAreas() : void{
		$areas = [];
		foreach($this->areas as $area){
			$areas[] = ["name" => $area->getName(), "desc" => $area->getDesc(), "flags" => $area->getFlags(), "pos1" => [$area->getFirstPosition()->getFloorX(), $area->getFirstPosition()->getFloorY(), $area->getFirstPosition()->getFloorZ()] , "pos2" => [$area->getSecondPosition()->getFloorX(), $area->getSecondPosition()->getFloorY(), $area->getSecondPosition()->getFloorZ()], "level" => $area->getLevelName(), "whitelist" => $area->getWhitelist(), "commands" => $area->getCommands(), "events" => $area->getEvents()];
		}
		file_put_contents($this->getDataFolder() . "areas.json", json_encode($areas));
	}

    /**  Festival Console Sign Flag for developers
     *   makes it easy to find Festival console output fast
     */
    public function codeSigned(){

        $this->getLogger()->info( "by -----------.------------" );
        $this->getLogger()->info( "  ,-. ,-. ,-. |-. ,-. . .  " );
        $this->getLogger()->info( "  | | |-' | | | | | | | |  " );
        $this->getLogger()->info( "  `-| `-' ' ' `-' `-' `-|  " );
        $this->getLogger()->info( "--`-'-----------------`-'--" );

    }
}
