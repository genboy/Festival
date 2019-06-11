<?php declare(strict_types = 1);
/**
 * src/genboy/Festival/FormUI.php
 */
namespace genboy\Festival;

use genboy\Festival\Festival;
use genboy\Festival\Area as FeArea;
use xenialdan\customui\CustomForm;
use xenialdan\customui\SimpleForm;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;

class FormUI{

    private $plugin;

    /** __construct
	 * @param Festival
     */
	public function __construct(Festival $plugin){
		$this->plugin = $plugin;
	}

    /** openUI
     * @class formUI
     * @func formUI->selectForm
	 * @param Player $user
     */
    public function openUI($user){

        if( $user->hasPermission("festival.access" ) ){
            //$user->sendMessage("Forms in development!"); # Sends to the sender
            $this->selectForm($user);
        }else{
            //$user->sendMessage("No permission to use this!"); # Sends to the sender
            return true;
        }
        return true;

    }

     /** selectForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function selectForm( Player $sender, $msg = false ) : void {
        $form = new SimpleForm(function ( Player $sender, ?int $data ) {
            if( $data === null){
                return;
            }
            switch ($data) {
                case 0:
                    $this->areaTPForm( $sender );
                break;
                case 1:
                    $this->areaSelectForm( $sender );
                break;
                case 2:
                    $this->levelForm( $sender );
                break;
                case 3:
                default:
                    $this->configForm( $sender );
                break;
            }
            return false;
        });

        $form->setTitle("Festival Manager");
        if($msg){
            $form->setContent($msg);
        }else{
            $form->setContent("Select an option");
        }

        // teleport to area
        $form->addButton("Area Teleport", 0, "textures/items/sign");
        $form->addButton("Area Management", 0, "textures/blocks/stonebrick_carved");
        $form->addButton("Level Management", 0, "textures/items/name_tag");
        $form->addButton("Configuration", 0, "textures/blocks/command_block");

        $form->sendToPlayer($sender);

    }


     /** areaSelectForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function areaSelectForm( Player $sender, $msg = false ) : void {
        $form = new SimpleForm(function ( Player $sender, ?int $data ) {
            if( $data === null){
                return;
            }
            switch ($data) {
               case 0:
                    $this->areaEditForm( $sender );
                break;
                case 1:
                    $this->areaCommandForm( $sender );
                break;
                case 2:
                    $this->areaWhitelistForm( $sender );
                break;
                case 3:
                    $this->areaNewForm( $sender ); // new
                break;
                case 4:
                    $this->areaDeleteForm( $sender ); // del
                break;
                default:
                    $this->selectForm( $sender );
                break;
            }
            return false;
        });

        $form->setTitle("Festival Area Manager");
        if($msg){
            $form->setContent($msg);
        }else{
            $form->setContent("Select an option");
        }

        // edit area flags
        $form->addButton("Edit area flags", 0, "textures/items/diamond_pickaxe");

        // edit area commands
        $form->addButton("Edit area commands", 0, "textures/blocks/command_block");

        // edit area whitelist
        $form->addButton("Edit area whitelist", 0, "textures/items/book_written");

        // new area
        $form->addButton("Create new area", 0, "textures/blocks/stonebrick_carved");

        // delete area
        $form->addButton("Delete an area", 0, "textures/blocks/pumpkin_face_off");



        $form->addButton("Go back");

        $form->sendToPlayer($sender);

    }

    /** configForm
     * @class formUI
	 * @param Player $sender
     */
    public function configForm( Player $sender ) : void {

        $form = new CustomForm(function ( Player $sender, ?array $data ) {
            if( $data === null){ // catch data and do something
                return;
            }
            //var_dump($data);

            $this->plugin->config["options"]["itemid"] = $data["itemid"];

            $msgpos_opt = ["msg", "title", "tip", "pop"];
            $this->plugin->config["options"]["msgpos"] = $msgpos_opt[ $data["msgpos"] ];

            $msgdsp_opt = ["on", "op", "off"];
            $this->plugin->config["options"]["msgdsp"] = $msgdsp_opt[ $data["msgdsp"] ];

            $areadsp_opt = ["on", "op", "off"];
            $this->plugin->config["options"]["areadsp"] = $areadsp_opt[ $data["areadsp"] ];

            /*$newautolist = "off";
            if(  $data["autolist"] == true){
                $newautolist = "on";
            }*/
            $this->plugin->config["options"]["autolist"] =  $data["autolist"];

            $c = 5; // after 5 options all input are flags
            foreach( $this->plugin->config["defaults"] as $flag => $set){
                $c++;
                $defaults[$flag] = $data[$c];
            }

            $this->plugin->config["defaults"] = $defaults;
            $this->plugin->helper->saveConfig( $this->plugin->config );

            $msg = "Configs saved!";
            $this->selectForm($sender, $msg);

        });

        $optionset = $this->plugin->config["options"];

        $form->setTitle("Festival Configuration");
        $form->addLabel("Config Options & default flags");

        $msgpos_tlt = "Area messages position";
        $msgpos_opt = ["msg", "title", "tip", "pop"];
        $msgpos_slc = array_search( $optionset["msgpos"], $msgpos_opt);
        $form->addStepSlider( $msgpos_tlt, $msgpos_opt, $msgpos_slc, "msgpos" );

        $msgdsp_tlt = "Area messages visible";
        $msgdsp_opt = ["on", "op", "off"];
        $msgdsp_slc = array_search( $optionset["msgdsp"], $msgdsp_opt);
        $form->addStepSlider( $msgdsp_tlt, $msgdsp_opt, $msgdsp_slc, "msgdsp" );

        $areadsp_tlt = "Area titles visible";
        $areadsp_opt = ["on", "op", "off"];
        $areadsp_slc = array_search( $optionset["areadsp"], $areadsp_opt);
        $form->addStepSlider( $areadsp_tlt, $areadsp_opt, $areadsp_slc, "areadsp" );

        $autolist = false;
        if( $optionset["autolist"] != false){
            $autolist = true;
        }
        $form->addToggle("Auto whitelist", $autolist, "autolist" );

        $nr = $optionset['itemid'];
        $form->addInput( "Action item", "block itemid", "$nr", "itemid" );


        foreach( $this->plugin->config["defaults"] as $flag => $set){
            $form->addToggle( $flag, $set );
        }
        $form->sendToPlayer($sender);

    }


    /** areaEditForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function areaEditForm( Player $sender , $input = false, $msg = false) : void {

        if( $input != false && isset( $input["selectedArea"] ) ){
            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["selectedArea"]];
            $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] = $areaname;

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] ) ){
                    $areaname = $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"];
                    unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] );
                }
                if( isset( $this->plugin->areas[ $areaname ] ) ){
                    $area = $this->plugin->areas[ $areaname ];

                    if( isset( $data["newareadesc"] ) && !empty( $data["newareadesc"] ) ){
                        $area->setDesc( $data["newareadesc"] );
                    }
                    $c = 1;
                    $flagset = $area->getFlags();
                    foreach( $flagset as $nm => $set){
                        if( isset( $data[$c] ) ){
                            $area->setFlag( $nm, $data[$c] );
                        }
                        $c++;
                    }
                    $area->save();
                    $this->plugin->helper->saveAreas();
                    $this->selectForm( $sender, "Area ". $areaname . " saved! Select an option"  );
                }else{
                    $this->areaForm( $sender, "Area ". $areaname . " not found! Try again, select an option" );
                }
                return false;
            });
            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["selectedArea"]];
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage area " . TextFormat::DARK_PURPLE . $areaname );
            //$form->addInput("Name", "Area name (id)", $this->plugin->areas[$areaname]->getNAme(), "newareaname" );
            $form->addInput("Description", "Area description", $this->plugin->areas[$areaname]->getDesc(), "newareadesc" );
            $flgs = $this->plugin->areas[$areaname]->getFlags();
            foreach( $flgs as $flag => $set){
                $form->addToggle( $flag, $set );
            }
            $form->sendToPlayer($sender);

        }else{

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->areaEditForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage area's");
            if($msg){
                $form->addLabel( $msg);
            }
            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( "Select to edit an area", $options, $slct, "selectedArea");
            $form->sendToPlayer($sender);

       }
    }


    /** areaCommandForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function areaCommandForm( Player $sender , $input = false, $msg = false) : void {

        if( $input != false && ( isset( $input["selectedArea"] ) || isset( $input["newcommand"] ) ) ){

            $areasnames = $this->plugin->helper->getAreaNameList();

            if( isset( $input["selectedArea"] ) ){
                $areaname = $areasnames[$input["selectedArea"]];
                $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] = $areaname;
            }

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] ) ){
                    $areaname = $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"];
                    $area = $this->plugin->areas[strtolower($areaname)];
                    unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] );
                }

                if( isset( $data["newcommand"] ) && $data["newcommand"] != "" && isset( $data["newcommandevent"] ) ){

                    // new
                    $event_opt = $data["newcommandevent"];
                    $msgdsp_opt = ["enter", "center", "leave"];
                    $event = $msgdsp_opt[$event_opt];
                    $clist = $area->getCommands();
                    $newcmd = $data["newcommand"];
                    $id = count($clist);

                    if( isset($area->events[$event]) ){
					   $eventarr = explode(",", $area->events[$event] );
                       $eventarr[] = $id;
					   $eventstr = implode(",", $eventarr );
				       $this->plugin->areas[strtolower($areaname)]->events[$event] = $eventstr;
                    }else{
                        $this->plugin->areas[strtolower($areaname)]->events[$event] = "$id";
                    }

                    $this->plugin->areas[strtolower($areaname)]->commands[$id] = $newcmd;

					$this->plugin->helper->saveAreas();

                    $this->areaSelectForm( $sender, "Area ". $areaname . " new $event command $id saved! Select an option"  );

                }else{
                    // delete
                    if( isset( $data["delcommand"] ) && $data["delcommand"] != ""  ){
                        $id = $data["delcommand"];
                        if( isset($this->plugin->areas[strtolower($areaname)]->commands[$id]) ){
                            unset($this->plugin->areas[strtolower($areaname)]->commands[$id]);
                            $this->plugin->helper->saveAreas();
                            $this->areaSelectForm( $sender, "Area ". $areaname . " command ". $id ." deleted! Select an option"  );
                        }else{
                            $this->areaSelectForm( $sender, "Command id not found! Try again or select another option"  );
                        }
                    }else{
                        $this->areaSelectForm( $sender, "Command empty and not saved! Try again or select another option"  );
                    }

                }



            });

            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["selectedArea"]];
            $area = $this->plugin->areas[strtolower($areaname)];

            $form->setTitle( TextFormat::DARK_PURPLE . "Commands for area " . TextFormat::DARK_PURPLE . $areaname );


            $form->addLAbel( "-------  Area command list: -------");

            foreach($area->events as $type => $list){
				if( trim($list,",") != "" ){
                    $form->addLabel("$type :");
                    $cmds = explode(",", trim($list,",") );
                    $clist = $area->getCommands();
                    foreach( $cmds as $ci ){
                        if(isset($area->commands[$ci])){
                            $com =$area->commands[$ci];
                            $form->addLabel("$ci: $com");
                        }
                    }
                }
            }
            $form->addLAbel( "-------- Add new command: --------");

            $msgdsp_tlt = "Add command event type";
            $msgdsp_opt = ["enter", "center", "leave"];
            $form->addStepSlider( $msgdsp_tlt, $msgdsp_opt, 0, "newcommandevent" );

            $form->addInput("New command:", "add new Command (without / )", "", "newcommand" );


            $form->addLAbel( "-------- Delete command: --------");

            $form->addInput("Type id here to delete:", "input command id to delete", "", "delcommand" );


            $form->sendToPlayer($sender);


        }else{

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->areaCommandForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage area commands");
            if($msg){
                $form->addLabel( $msg);
            }
            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( "Select area", $options, $slct, "selectedArea");
            $form->sendToPlayer($sender);

       }

    }


    /** areaWhitelistForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function areaWhitelistForm( Player $sender , $input = false, $msg = false) : void {

        if( $input != false && isset( $input["selectedArea"] ) ){

            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["selectedArea"]];
            $area = $this->plugin->areas[strtolower($areaname)];

            if( isset( $input["selectedArea"] ) ){
                $areaname = $areasnames[$input["selectedArea"]];
                $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] = $areaname;
            }

                $form = new CustomForm(function ( Player $sender, ?array $data ) {

                    if( $data === null){
                        return;
                    }
                    if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] ) ){
                        $areaname = $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"];
                        $area = $this->plugin->areas[strtolower($areaname)];
                        unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] );
                    }
                    $players = $this->plugin->players;
                    $list = $area->getWhitelist();
                    $c = 0;
                    foreach( $players as $nm => $player){
                        if( $data[$c] ){
                            var_dump($data[$c]);
                            $area->setWhitelisted($nm);
                        }else{
                            $area->setWhitelisted($nm,false);
                        }
                    }
                    $this->areaWhitelistForm( $sender, false, "Whitelist saved." );


                });


                $form->setTitle( TextFormat::DARK_PURPLE . "Manage area whitelist");

                if($msg){
                    $form->addLabel( $msg);
                }
                $players = $this->plugin->players;
                $list = $area->getWhitelist();
                foreach( $players as $nm => $player){
                    $set = false;
                    if( in_array( $nm, $list ) ){
                        $set = true;
                    }
                    $form->addToggle( $nm, $set );
                }
                $form->sendToPlayer($sender);

        }else{

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->areaWhitelistForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage area whitelist");
            if($msg){
                $form->addLabel( $msg);
            }else{
                $form->addLabel( "Select whitelist area");
            }
            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( "Select area", $options, $slct, "selectedArea");
            $form->sendToPlayer($sender);

        }

    }

    /** areaNewForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function areaNewForm( Player $sender , $input = false, $msg = false) : void {
        if( $input != false ){
            if( isset($input["type"]) && ( !isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["newname"] ) ||  $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["newname"] == "" ) ){
                $form = new CustomForm(function ( Player $sender, ?ARRAY $data ) {
                    if( $data === null){
                        $sender->sendMessage("Form data corrupted or not available, please try again.");
                    }else{

                        if( isset( $data["name"] ) && $data["name"] != "" && !isset( $this->plugin->areas[ $data["name"] ] ) ){ // check and save area
                            if( !isset($data["desc"]) ){
                                $data["desc"] = $data["name"];
                            }
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["name"] = $data["name"];
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["desc"] = $data["desc"];
                            $newarea = $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]; //var_dump($newarea);
                            unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"] );

                            $newarea["level"] = $sender->getLevel()->getName(); // full levelname incl. uppercase etc.
                            if( $newarea["type"] == "cube" ){
                                $newarea["radius"] = 0;
                            }
                            if( isset( $this->plugin->levels[ strtolower( $newarea["level"]) ] ) ){
                                $level = $this->plugin->levels[ strtolower( $newarea["level"]) ];
                                $newarea["flags"] = $level->getFlags();
                            }else{
                                $newarea["flags"] = $this->plugin->defaults;
                            }

                            new FeArea( $newarea["name"], $newarea["desc"], $newarea["flags"], $newarea["pos1"], $newarea["pos2"], $newarea["radius"], $newarea["level"], [], [], [], $this->plugin);
                            $this->plugin->helper->saveAreas();
                            $this->areaSelectForm( $sender, "New area named ".$newarea["name"]." created!"  );

                        }else{
                            $this->areaNewForm( $sender , $data, $msg = "New area name not correct or allready used. Please try another name:");
                        }
                    }
                });

                $form->setTitle("Festival Area Maker");
                if($msg){
                    $form->addLabel($msg);
                }else{
                    $form->addLabel("Create area");
                }
                $form->addInput( "Area name", "area name", "", "name" );
                $form->addInput( "Area decription", "area description", "", "desc" );
                $form->sendToPlayer($sender);
            }
        }else{

            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"] = [];
            // simple form select cube or sphere
            $form = new SimpleForm(function ( Player $sender, ?int $data ) {
                if( $data === null){
                    $sender->sendMessage("Form data corrupted or not available, please try again.");
                }else{
                    switch ($data) {
                        case 0:
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] = "cube";
                            $o = TextFormat::GREEN . "Tab position 1 for new cube area (right mouse block place)";
                            $sender->sendMessage($o);
                        break;
                        case 1:
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] = "sphere";
                            $o = TextFormat::GREEN . "Tab the center position for the new sphere area (right mouse block place)";
                            $sender->sendMessage($o);
                        break;
                        case 2:
                            $this->areaSelectForm( $sender ); // goback
                        break;
                        default:
                            $this->areaSelectForm( $sender ); // goback
                        break;
                    }
                }
            });

            $form->setTitle("Festival Area Maker");
            if($msg){
                $form->setContent($msg);
            }else{
                $form->setContent("Select new area type");
            }
            $form->addButton("Cube area (select 2 positions)"); // cube area
            $form->addButton("Sphere Area (select center and radius)"); // sphere area
            $form->addButton("Go back");
            $form->sendToPlayer($sender);
        }
    }

    /** areaDeleteForm
     * @class formUI
	 * @param Player $sender
	 * @param arr $input
	 * @param string $msg
     */
    public function areaDeleteForm( Player $sender , $input = false, $msg = false) : void {

        if( $input != false && isset( $input["deleteArea"] ) ){
            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["deleteArea"]];
            $this->plugin->players[ strtolower( $sender->getName() ) ]["del"] = $areaname;
            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["del"] ) ){
                    $areaname = $this->plugin->players[ strtolower( $sender->getName() ) ]["del"];
                    unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["del"] );
                }
                if( isset( $this->plugin->areas[ $areaname ] ) ){
                    $area = $this->plugin->areas[ $areaname ];
                    $area->delete();
                    $this->plugin->helper->saveAreas();
                    $this->selectForm( $sender, "Area ". $areaname . " deleted! Select an option"  );
                }else{
                    $this->areaForm( $sender, "Area ". $areaname . " not found, sorry. Select an option" );
                }
                return false;
            });
            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["deleteArea"]];
            $form->setTitle( TextFormat::RED . "! Delete area " . TextFormat::WHITE . $areaname );
            $form->addLabel( TextFormat::RED ."You are going to delete area ".  $areaname );
            $form->sendToPlayer($sender);
        }else{
            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->areaDeleteForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . "Delete an area");
            if($msg){
                $form->addLabel( $msg);
            }else{
                $form->addLabel( "Select area to delete");
            }
            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( "Select to delete area", $options, $slct, "deleteArea");
            $form->sendToPlayer($sender);
       }
    }

    /** levelForm  (prototype function setup)
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function levelForm( Player $sender , $inputs = false, $msg = false) : void {

        if( $inputs != false && isset( $inputs["selectedLevel"] ) ){
            // manage level flags
            $levels = $this->plugin->helper->getServerWorlds();
            $levelname = $levels[ $inputs["selectedLevel"] ];
            $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] = $levelname;

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $levels = $this->plugin->helper->getServerWorlds();
                if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] ) ){
                    $levelname = $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"];
                    unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] );
                }
                if( isset( $this->plugin->levels[ strtolower($levelname) ] ) ){
                    $lvl = $this->plugin->levels[ strtolower($levelname) ];
                    $flagset = $lvl->getFlags();
                    $c = 0;
                    foreach( $flagset as $nm => $set){
                        if( isset( $data[$c] ) ){
                            $lvl->setFlag( $nm, $data[$c] );
                        }
                        $c++;
                    }
                    $lvl->save();
                    $this->plugin->helper->saveLevels();
                    $this->selectForm( $sender, "Level ". $levelname . " flagset saved! Select an option"  );
                }else{
                    // add new level configs?
                    $worlds = $this->plugin->helper->getServerWorlds();
                    if( in_array( strtolower($levelname), $worlds ) ){
                        var_dump($data);
                        $this->levelForm( $sender, false, "Level ". $levelname . " not found! Try again, select an option" );

                    }else{
                        $this->levelForm( $sender, false, "Level ". $levelname . " not found! Try again, select an option" );
                    }
                }
                return false;
            });

            $levels =$this->plugin->helper->getServerWorlds();
            $levelname = $levels[$inputs["selectedLevel"]];
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage level flags " . TextFormat::DARK_PURPLE . $levelname );

            $flgs = $this->plugin->levels[strtolower($levelname)]->getFlags();
            foreach( $flgs as $flag => $set){
                $form->addToggle( $flag, $set );
            }
            $form->sendToPlayer($sender);

        }else{ // select level
            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->levelForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage levels");
            if( $msg ){
                $form->addLabel( $msg );
            }else{
                $form->addLabel( "Select level to edit flags");
            }
            $levels = $this->plugin->helper->getServerWorlds();
            $current = $sender->getLevel()->getName();
            $slct = array_search( $current, $levels);
            $form->addDropdown( "Level select", $levels, $slct, "selectedLevel");
            $form->sendToPlayer($sender);
       }
    }

    /** areaTPForm
     * @class formUI
	 * @param Player $sender
     */
    public function areaTPForm( Player $sender ) : void {
        $form = new CustomForm(function ( Player $sender, ?array $data ) {
            if( $data === null){
                return;
            }
            //var_dump($data);
            if( isset( $data[0] ) ){
                $selectlist = array();
                foreach($this->plugin->areas as $area){
                    $selectlist[]= strtolower( $area->getName() );
                }
                if(  $selectlist[ $data[0] - 1 ] ){
                    $areaname = $selectlist[ $data[0] - 1 ];
                    Server::getInstance()->dispatchCommand($sender, "fc tp ".$areaname );
                }
            }
        });

        $form->setTitle("Teleport to Area");
        $selectlist = array();
        $selectlist[]= "Select destination area";
        foreach($this->plugin->areas as $area){
            $selectlist[]= strtolower( $area->getName() );
        }
        $form->addDropdown("TP to area", $selectlist );
        $form->sendToPlayer($sender);
    }
}
