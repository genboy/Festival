<?php declare(strict_types = 1);
/** src/genboy/Festival2/Helper.php
 *
 * global helper
 *
 */
namespace genboy\Festival;

use genboy\Festival\Festival;
use genboy\Festival\lang\Language;
use genboy\Festival\Level as FeLevel;
use genboy\Festival\Area as FeArea;

use pocketmine\math\Vector3;

class Helper {

    private $plugin;

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        if(!is_dir($this->plugin->getDataFolder())){
            @mkdir($this->plugin->getDataFolder());
		        }
        // add resource folder for backwards compatibility
        if( !is_dir($this->plugin->getDataFolder().'resources') ){
           @mkdir($this->plugin->getDataFolder().'resources');
		}
    }



    /** loadAreas
	 * @file resources/areas.json
     * @func this getSource
	 * @var obj FeArea
	 * @param array $data
     */
    public function loadAreas(): bool{
        // create a list of current areas from saved json
        $adata = $this->getDataSet( "areas" );
        //
        $worlds = $this->getServerWorlds();

        if( isset($adata) && is_array($adata) ){

            foreach($adata as $area){

                if( !isset($area["priority"]) ){
                    $area["priority"] = 0;
                }
                if( !isset($area["radius"]) ){
                    $area["radius"] = 0;
                }
                if( !isset($area["top"]) ){
                    $area["top"] = 0;
                }
                if( !isset($area["bottom"]) ){
                    $area["bottom"] = 0;
                }
                $newflags = []; // translated to new flag names
                foreach( $area["flags"] as $f => $set ){
                    $flagname = $this->isFlag( $f );
                    $newflags[$flagname] = $set;
                }

                // check if level excists
                if( in_array( $area["level"], $worlds ) ){
                    new FeArea($area["name"], $area["desc"], $area["priority"], $newflags, new Vector3($area["pos1"]["0"], $area["pos1"]["1"], $area["pos1"]["2"]), new Vector3($area["pos2"]["0"], $area["pos2"]["1"], $area["pos2"]["2"]), $area["radius"], $area["top"], $area["bottom"], $area["level"], $area["whitelist"], $area["commands"], $area["events"], $this->plugin);
                }

            }
            //$this->plugin->getLogger()->info( "Festival has ".count($adata)." area's set!" );

            $this->saveAreas(); // make sure recent updates are saved

            $ca = 0; // plugin area command count
            $fa = 0; // plugin area flag count
            foreach( $this->plugin->areas as $a ){
                foreach($a->flags as $flag){
                    if($flag){
                        $fa++;
                    }
                }
                $ca = $ca + count( $a->getCommands() );
            }
            $levelsloaded = count( $worlds );
            $this->plugin->getLogger()->info( $fa.' '.Language::translate("flags").' '.Language::translate("select-and").' '. $ca .' '. Language::translate("cmds") .' '.Language::translate("select-in").' '. count($this->plugin->areas)  .' '.  Language::translate("areas").' ('.$levelsloaded.' '.Language::translate("levels").')');
            return true;
        }
        return false;
    }

    /** Save areas
	 * @var obj Festival
	 * @var obj FeArea
	 * @file areas.json
	 */
	public function saveAreas() : void{
        // save current areas to json
		$areas = [];
        if( isset($this->plugin->areas) && is_array($this->plugin->areas) ){

            foreach($this->plugin->areas as $area){
                $areas[] = ["name" => $area->getName(), "desc" => $area->getDesc(), "priority" => $area->getPriority(), "flags" => $area->getFlags(), "pos1" => [$area->getFirstPosition()->getFloorX(), $area->getFirstPosition()->getFloorY(), $area->getFirstPosition()->getFloorZ()] , "pos2" => [$area->getSecondPosition()->getFloorX(), $area->getSecondPosition()->getFloorY(), $area->getSecondPosition()->getFloorZ()], "radius" => $area->getRadius(), "top" => $area->getTop(), "bottom" => $area->getBottom(), "level" => $area->getLevelName(), "whitelist" => $area->getWhitelist(), "commands" => $area->getCommands(), "events" => $area->getEvents()];
            }

            $this->saveDataSet( "areas", $areas );

        }
    }

    /** getAreaListSelected
     * @func this saveDataSet []
     */
    public function getAreaNameList( $sender = false, $cur = false ){
        // default array empty
        $options = [];
        $slct = 0;
        $c = 0;
        foreach( $this->plugin->areas as $nm => $area ){
            if($cur != false && $sender != false){
                if( isset( $this->plugin->inArea[strtolower( $sender->getName() )][$nm] )  ){
                    $slct = $c;
                }
            }
            $options[] = $nm;
            $c++;
        }
        $lst = $options;
        if($cur != false){
            $lst = [ $options, $slct ];
        }
        return $lst;
    }



    /** getServerInfo
	 * @func plugin getServer()
     */
    public function getServerInfo() : ARRAY {
        $s = [];
        $s['ver']   = $this->plugin->getServer()->getVersion();
        $s['api']   = $this->plugin->getServer()->getApiVersion();
        return $s;
    }


    /** getServerWorlds
	 * @func plugin getServer()->getDataPath()
	 * @dir worlds
     */
    public function getServerWorlds() : ARRAY {
        $worlds = [];
        $worldfolders = array_filter( glob($this->plugin->getServer()->getDataPath() . "worlds/*") , 'is_dir');
        foreach( $worldfolders as $worldfolder) {
            $worlds[] = basename($worldfolder);
            $worldfolder = str_replace( $worldfolders, "", $worldfolder);
            if( $this->plugin->getServer()->isLevelLoaded($worldfolder) ) {
                continue;
            }
            /* Load all world levels
            if( !empty( $worldfolder ) ){
                $this->plugin->getServer()->loadLevel($worldfolder);
            } */
        }
        return $worlds;
    }


     /** saveConfig
	 * @class Helper
	 * @file resources/config.json
	 * @param array $data
     */
    public function saveConfig( $data ){
        $this->plugin->config = $data;
        $this->saveDataSet( 'config', $data );
    }


    /** newConfigPreset
     * @return array[ options, defaults ]
     */
    public function newConfigPreset() : ARRAY {
        // world / area defaults
        $c = [
        'options' =>[
            'lang'              => "en",    // Language en/nl/pl/es
            'itemid'            =>  201,    // Purpur Pillar itemid key held item
            'msgdisplay'        => 'on',    // msg display off,op,listed,on
            'msgposition'       => 'tip',   // msg position msg,title,tip,pop
            'areatitledisplay'  => 'on',    // area title display off,op,listed,on
            'autowhitelist'     => 'off',    // area creator auto whitelist off,on
            'flightcontrol'     => 'on',    // area fly-flag active off,on
            'levelcontrol'      => 'off',    // area level flags active off,on
            //'compass'           => 'off',    // area level flags active off,on
        ],
        'defaults' =>[
            'perms'     => false,
            'pass'      => false,    // previous passage(barrier) flag
            'msg'       => false,
            'edit'      => true,
            'touch'     => false,
            'flight'    => false,
            'hurt'      => false,    // previous god flag
            'fall'      => false,    // previous falldamage flag
            'explode'   => true,
            'tnt'       => true,
            'fire'      => false,
            'shoot'     => false,
            'pvp'       => false,
            'effect'    => false,    // previous effect flag
            'hunger'    => false,
            'drop'      => false,
            'mobs'      => false,
            'animals'   => false,
            'cmd'       => false,   // previous cmdmode flag
        ]];
        return $c;
    }

   /** formatOldConfigs
	 * @param array $c
	 * @func plugin getLogger()
     * @func $this isFlag()
     * @func $this loadLevels
     * @func file_put_contents
     * @return array
     */
    public function formatOldConfigs( $c ) : ARRAY {
        $p = $this->newConfigPreset();
        // overwrite default presets
        $p['options']['lang'] = "en";
        if( isset( $c['Options']['Language'] ) ){
          $p['options']['lang'] = $c['Options']['Language'];
        }
        $p['options']['itemid'] = 201; // purpur_block
        if( isset( $c['Options']['ItemID'] ) ){
          $p['options']['itemid'] = $c['Options']['ItemID'];
        }
        if( isset( $c['Options']['Msgdisplay'] ) ){
            $p['options']['msgdisplay'] = "off";
            if( $c['Options']['Msgdisplay'] == true || $c['Options']['Msgdisplay'] == "on" ){
                $p['options']['msgdisplay'] = "on";
            }
        }
        if( isset( $c['Options']['Msgtype'] ) ){
          $p['options']['msgposition'] = $c['Options']['Msgtype'];
        }
        if( isset( $c['Options']['Areadisplay'] ) ){
          $p['options']['areatitledisplay'] = $c['Options']['Areadisplay'];
        }
        $p['options']['autowhitelist'] = "off";
        if( isset( $c['Options']['AutoWhitelist'] ) ){
          $p['options']['autowhitelist'] = $c['Options']['AutoWhitelist'];
        }
        $p['options']['flightcontrol'] = "off";
        if( isset( $c['Options']['FlightControl'] ) ){
          $p['options']['flightcontrol']  = $c['Options']['FlightControl'];
        }
        $p['options']['levelcontrol'] = "off";
        if( isset( $c['Options']['LevelControl'] ) ){
          $p['options']['levelcontrol']  = $c['Options']['LevelControl'];
        }

        /*
        // compass option
        $p['options']['compass'] = "off";
        if( isset( $c['Options']['Compass'] ) ){
          $p['options']['compass']  = $c['Options']['Compass'];
        }
        */



        if( isset( $c['Default'] ) && is_array( $c['Default'] ) ){
            foreach( $c['Default'] as $fn => $set ){
                $flagname = $this->isFlag( $fn );
                if( isset($p['defaults'][$flagname]) ){
                    $p['defaults'][$flagname] = $set;
                }
            }
        }


        if( !$this->loadLevels() ){ // no level.json available
            $worldlist = $this->getServerWorlds(); // available levels (world folderr)
            foreach( $worldlist as $ln){
                $desc = "Festival Area ". $ln;
                if( isset( $c['Worlds'][ $ln ] ) && is_array( $c['Worlds'][ $ln ] ) ){ // create level from old config
                    $lvlflags = $c['Worlds'][ $ln ]; //$c['Worlds'][ strtolower($ln) ];
                    $newflags = [];
                    foreach( $lvlflags as $f => $set ){
                        $flagname = $this->isFlag( $f );
                        $newflags[$flagname] = $set;
                    }
                    new FeLevel($ln, $desc, $p['options'], $newflags, $this->plugin);
                }else{ // create level from new config
                    $presets = $this->newConfigPreset();
                    new FeLevel($ln, $desc, $presets['options'], $presets['defaults'], $this->plugin);
                }
            }
            //$this->plugin->getLogger()->info( "Configure level data.." ); //.. before translation is known..
            $this->saveLevels( $this->plugin->levels );
        }
        return $p;

    }

    /** loadLevels
	 * @file resources/levels.json
     * @var plugin levels
	 * @class FeLevel
     */
    public function loadLevels(): bool{

        // create a list of current levels from saved json
        $ldata = $this->getDataSet( "levels" );

        if( isset($ldata) && is_array($ldata) && !empty($ldata[0])){
            foreach($ldata as $level){
                /*
                // new compass option
                if( !isset( $level["options"]['compass'] ) ){
                    $level["options"]['compass'] = "off";
                }
                */
                new FeLevel($level["name"], $level["desc"], $level["options"],$level["flags"], $this->plugin);
            }
            $this->plugin->getLogger()->info( "Level data set loaded!" );
            $this->saveLevels( $this->plugin->levels );
            return true;
        }
        return false;
    }

    /** saveLevels
	 * @file resources/levels.json
     * @var plugin levels
	 * @param array $data
     */
    public function saveLevels(): void{
        // save current levels to json
        foreach($this->plugin->levels as $level){
            $levels[] = [ "name" => $level->getName(), "desc" => $level->getDesc(), "options" => $level->getOptions(), "flags" => $level->getFlags() ];
        }
        $this->saveDataSet( 'levels', $levels );
    }

    /** loadDefaultLevels
	 * @var plugin config
	 * @file resources/levels.json
	 * @func plugin getLogger()
	 * @func this saveLevels()
     * @var plugin levels
	 * @class FeLevel
     */
    public function loadDefaultLevels(){
        // create a list of current levels with loaded configs
        $config  = $this->plugin->config;

        $worldlist = $this->getServerWorlds();
        if( is_array( $worldlist ) ){
            new FeLevel("DEFAULT", "Default world level", $config['options'], $config['defaults'], $this->plugin);

            foreach( $worldlist as $ln){
                $desc = "Festival Level ". $ln;
                new FeLevel($ln, $desc, $config['options'], $config['defaults'], $this->plugin);
            }

            $this->plugin->getLogger()->info( "Default level data set loaded!" );
            $this->saveLevels();
        }
    }

    /** loadDefaultAreas
     * @func this saveSource []
     */
    public function loadDefaultAreas(){
        // default array empty
        $this->saveDataSet( "areas", [] );
    }



    /** isFlag
     * @param string
     * @return string
     */
    public function isFlag( $str ) : string {
        // flag names
        $names = [
            "god","God","save","hurt",
            "pvp","PVP",
            "flight", "fly",
            "edit","Edit","build","break","place",
            "touch","Touch","interact",
            "mobs","Mobs","mob",
            "animals","Animals","animal",
            "effects","Effects","magic","effect",
            "tnt","TNT",
            "explode","Explode","explosion","explosions",
            "fire","Fire","fires","burn",
            "hunger","Hunger","starve",
            "drop","Drop",
            "msg","Msg","message",
            "passage","Passage","pass","barrier",
            "perms","Perms","perm",
			"falldamage","Falldamage","nofalldamage","fd","nfd","fall",
            "shoot","Shoot", "launch",
            "cmdmode","CMD","CMDmode","commandmode","cmdm", "cmd",
        ];
        $str = strtolower( $str );
        $flag = false;
        if( in_array( $str, $names ) ) {
            $flag = $str;
            if( $str == "save" || $str == "hurt" || $str == "god"){
                $flag = "hurt";
            }
            if( $str == "fly" || $str == "flight"){
                $flag = "flight";
            }
            if( $str == "build" || $str == "break" || $str == "place" || $str == "edit"){
                $flag = "edit";
            }
            if( $str == "touch" || $str == "interact" ){
                $flag = "touch";
            }
            if( $str == "animals" || $str == "animal" ){
                $flag = "animals";
            }
            if( $str == "mob" || $str == "mobs"  ){
                $flag = "mobs";
            }
            if( $str == "magic" || $str == "effects" || $str == "effect" ){
                $flag = "effect";
            }
            if( $str == "message"  || $str == "msg"){
                $flag = "msg";
            }
            if( $str == "perm"  || $str == "perms" ){
                $flag = "perms";
            }
            if( $str == "passage" || $str == "barrier" || $str == "pass" ){
                $flag = "pass";
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
            if( $str == "effect" || $str == "effects" || $str == "magic"){
                $flag = "effect";
            }
            if( $str == "hunger" || $str == "starve" ){
                $flag = "hunger";
            }
			if( $str == "nofalldamage" || $str == "falldamage" || $str == "fd" || $str == "nfd" || $str == "fall"){
				$flag = "fall";
			}
            if( $str == "cmd" || $str == "cmdmode" || $str == "commandmode" || $str == "cmdm"){ // ! command is used as function..
                $flag = "cmd";
            }
        }
        return $flag;
    }


    /** getDataSet
	 * @param string $name
	 * @param (string $type)
	 * @func plugin getDataFolder()
     * @func yaml_parse_file
     * @func json_decode
     * @return array
     */
    public function getDataSet( $name , $type = 'json' ) : ARRAY {
        if( file_exists($this->plugin->getDataFolder() . $name . ".". $type)){
            switch( $type ){
                case 'yml':
                case 'yaml':
                    $data = yaml_parse_file($this->plugin->getDataFolder() . $name . ".yml"); // the old defaults
                break;
                case 'json':
                default:
                    $data = json_decode( file_get_contents( $this->plugin->getDataFolder() . $name . ".json" ), true );
                break;
            }
        }
        if( isset( $data ) && is_array( $data ) ){
            return $data;
        }
        return [];
    }

     /** saveDataSet
	 * @param string $name
	 * @param array $data
	 * @param string $type default
	 * @func plugin getDataFolder()
     * @func FileConfig
     * @func json_encode
     * @func file_put_contents
     * @return array
     */
    public function saveDataSet( $name, $data, $type = 'json') : ARRAY {
        switch( $type ){
            case 'yml':
            case 'yaml':
                 $src = new FileConfig($this->plugin->getDataFolder(). $name . ".yml", FileConfig::YAML, $data);
                 $src->save();
            break;
            case 'json':
            default:
		        file_put_contents( $this->plugin->getDataFolder() . $name . ".json", json_encode( $data ) );
            break;
        }
        return $this->getDataSet( $name , $type );
    }

    /** getSource
	 * @param string $name
	 * @param (string $type)
	 * @func plugin getDataFolder()
     * @func yaml_parse_file
     * @func json_decode
     * @return array
     */
    public function getSource( $name , $type = 'json' ) : ARRAY {
        if( file_exists($this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".". $type)){
            switch( $type ){
                case 'yml':
                case 'yaml':
                    $data = yaml_parse_file($this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".yml"); // the old defaults
                break;
                case 'json':
                default:
                    $data = json_decode( file_get_contents( $this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".json" ), true );
                break;
            }
        }
        if( isset( $data ) && is_array( $data ) ){
            return $data;
        }
        return [];
    }


    /** saveSource
	 * @param string $name
	 * @param array $data
	 * @param string $type default
	 * @func plugin getDataFolder()
     * @func FileConfig
     * @func json_encode
     * @func file_put_contents
     * @return array
     */
    public function saveSource( $name, $data, $type = 'json') : ARRAY {
        switch( $type ){
            case 'yml':
            case 'yaml':
                 $src = new FileConfig($this->plugin->getDataFolder(). "resources" . DIRECTORY_SEPARATOR . $name . ".yml", FileConfig::YAML, $data);
                 $src->save();
            break;
            case 'json':
            default:
		        file_put_contents( $this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".json", json_encode( $data ) );
            break;
        }
        return $this->getSource( $name , $type );
    }

    public function isPluginLoaded(string $pluginName){

        return ($findplugin = $this->plugin->getServer()->getPluginManager()->getPlugin($pluginName)) !== null and $findplugin->isEnabled();

    }

}
