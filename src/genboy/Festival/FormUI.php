<?php declare(strict_types = 1);
/**
 * src/genboy/Festival/FormUI.php
 */
namespace genboy\Festival;

use genboy\Festival\Festival;
use genboy\Festival\lang\Language;
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

        $form->setTitle( Language::translate("ui-festival-manager") ); // Festival Manager
        if($msg){
            $form->setContent($msg);
        }else{
            $form->setContent( Language::translate("ui-select-an-option") );
        }

        // teleport to area
        $form->addButton( Language::translate("ui-area-teleport"), 0, "textures/items/sign");
        $form->addButton( Language::translate("ui-area-management"), 0, "textures/blocks/stonebrick_carved");
        $form->addButton( Language::translate("ui-level-management"), 0, "textures/items/name_tag");
        $form->addButton( Language::translate("ui-config-management"), 0, "textures/blocks/command_block");

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
                    $this->areaNewForm( $sender ); // new
               break;
               case 1:
                    $this->areaEditForm( $sender );
                break;
                case 2:
                    $this->areaCommandForm( $sender );
                break;
                case 3:
                    $this->areaWhitelistForm( $sender );
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

        $form->setTitle( Language::translate("ui-area-manager") );
        if($msg){
            $form->setContent($msg);
        }else{
            $form->setContent( Language::translate("ui-select-an-option") );
        }


        // new area
        $form->addButton( Language::translate("ui-create-area"), 0, "textures/blocks/stonebrick_carved");

        // edit area flags
        $form->addButton( Language::translate("ui-edit-area-options"), 0, "textures/items/diamond_pickaxe");

        // edit area commands
        $form->addButton( Language::translate("ui-edit-area-commands"), 0, "textures/blocks/command_block");

        // edit area whitelist
        $form->addButton( Language::translate("ui-edit-area-whitelist"), 0, "textures/items/book_written");

        // delete area
        $form->addButton( Language::translate("ui-delete-area"), 0, "textures/blocks/pumpkin_face_off");

        $form->addButton( Language::translate("ui-go-back") );

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
            $this->plugin->config["options"]["msgposition"] = $msgpos_opt[ $data["msgposition"] ];

            $msgdsp_opt = ["on", "op", "off"];
            $this->plugin->config["options"]["msgdisplay"] = $msgdsp_opt[ $data["msgdisplay"] ];

            $areadsp_opt = ["on", "op", "off"];
            $this->plugin->config["options"]["areatitledisplay"] = $areadsp_opt[ $data["areatitledisplay"] ];

            $newautolist = "off";
            if(  $data["autowhitelist"] == true){
                $newautolist = "on";
            }
            $this->plugin->config["options"]["autowhitelist"] =  $newautolist;

            $newflightcontrol = "off";
            if(  $data["flightcontrol"] == true){
                $newflightcontrol = "on";
            }
            $this->plugin->config["options"]["flightcontrol"] =  $newflightcontrol;

            $c = 6; // after 5 options all input are flags
            foreach( $this->plugin->config["defaults"] as $flag => $set){
                $c++;
                $defaults[$flag] = $data[$c];
            }

            $this->plugin->config["defaults"] = $defaults;
            $this->plugin->helper->saveConfig( $this->plugin->config );

            $msg = Language::translate("ui-go-back") . "!";
            $this->selectForm($sender, $msg);

        });

        $optionset = $this->plugin->config["options"];

        $form->setTitle( Language::translate("ui-festival-configuration") );
        $form->addLabel( Language::translate("ui-config-flags-options") );

        $msgpos_tlt = Language::translate("ui-config-msg-position"); //"Area messages position";
        $msgpos_opt = ["msg", "title", "tip", "pop"];
        $msgpos_slc = array_search( $optionset["msgposition"], $msgpos_opt);
        $form->addStepSlider( $msgpos_tlt, $msgpos_opt, $msgpos_slc, "msgposition" );

        $msgdsp_tlt = Language::translate("ui-config-msg-visible"); //"Area messages visible";
        $msgdsp_opt = ["on", "op", "off"];
        $msgdsp_slc = array_search( $optionset["msgdisplay"], $msgdsp_opt);
        $form->addStepSlider( $msgdsp_tlt, $msgdsp_opt, $msgdsp_slc, "msgdisplay" );

        $areadsp_tlt = Language::translate("ui-config-floating-titles"); //"Area floating titles visible";
        $areadsp_opt = ["on", "op", "off"];
        $areadsp_slc = array_search( $optionset["areatitledisplay"], $areadsp_opt);
        $form->addStepSlider( $areadsp_tlt, $areadsp_opt, $areadsp_slc, "areatitledisplay" );

        $autolist = false;
        if( $optionset["autowhitelist"] === true || $optionset["autowhitelist"] == "on"){
            $autolist = true;
        }
        $form->addToggle( Language::translate("ui-config-auto-whitelist"), $autolist, "autowhitelist" );

        $flightcontrol = false;
        if( $optionset["flightcontrol"] === true || $optionset["flightcontrol"] == "on"){
            $flightcontrol = true;
        }
        $form->addToggle( Language::translate("ui-config-flight-control"), $flightcontrol, "flightcontrol" );

        $nr = $optionset['itemid'];
        $form->addInput( Language::translate("ui-config-action-itemid"), "block itemid", "$nr", "itemid" );


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

                    if( isset( $data["newareaname"] ) && !empty( $data["newareaname"] ) ){
                        $this->plugin->hideAreaTitle( $sender, $sender->getPosition()->getLevel(), $area );
                        $area->setName( $data["newareaname"] );
                    }
                    if( isset( $data["newareadesc"] ) && !empty( $data["newareadesc"] ) ){
                        $area->setDesc( $data["newareadesc"] );
                    }
                    if( isset( $data["newareapriority"] ) && !empty( $data["newareapriority"] ) ){
                        $area->setPriority( intval( $data["newareapriority"] ) );
                    }
                    $c = 3; // 3 variables, others are flags..
                    $flagset = $area->getFlags();
                    foreach( $flagset as $nm => $set){
                        if( isset( $data[$c] ) ){
                            $area->setFlag( $nm, $data[$c] );
                        }
                        $c++;
                    }
                    $area->save();
                    $this->plugin->helper->saveAreas();
                    $this->plugin->checkAreaTitles( $sender, $sender->getPosition()->getLevel() );
                    $this->selectForm( $sender, Language::translate("area") . " " . $areaname . " " . Language::translate("ui-saved") . " " . Language::translate("ui-select-an-option")  );
                }else{
                    $this->areaForm( $sender, Language::translate("area") . " " . $areaname . " " . Language::translate("ui-not-found") . " " . Language::translate("ui-try-again") . ". " . Language::translate("ui-select-an-option")  );
                }
                return false;
            });
            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["selectedArea"]];
            $form->setTitle( TextFormat::DARK_PURPLE . Language::translate("ui-manage-area") . " " . TextFormat::DARK_PURPLE . $areaname );
            //$form->addInput("Name", "Area name (id)", $this->plugin->areas[$areaname]->getNAme(), "newareaname" );
            $form->addInput( Language::translate("ui-name"), "Area name", $this->plugin->areas[$areaname]->getName(), "newareaname" );
            $form->addInput(Language::translate("ui-description"), "Area description", $this->plugin->areas[$areaname]->getDesc(), "newareadesc" );
            $form->addInput( Language::translate("ui-priority"), "Area priority", strval( $this->plugin->areas[$areaname]->getPriority() ), "newareapriority" );
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
            $form->setTitle( TextFormat::DARK_PURPLE . Language::translate("ui-manage-areas") );
            if($msg){
                $form->addLabel( $msg);
            }
            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( Language::translate("ui-select-edit-area"), $options, $slct, "selectedArea");
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
                    $area = $this->plugin->areas[$areaname];
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
				       $this->plugin->areas[$areaname]->events[$event] = $eventstr;
                    }else{
                        $this->plugin->areas[$areaname]->events[$event] = "$id";
                    }

                    $this->plugin->areas[$areaname]->commands[$id] = $newcmd;

					$this->plugin->helper->saveAreas();

                    $this->areaSelectForm( $sender, Language::translate("area") . " ". $areaname . " " . Language::translate("ui-new") . " " . $event . " " . Language::translate("cmd") . " " .  $id . " " .  Language::translate("ui-saved"). " ". Language::translate("ui-select-an-option")  );

                }else{

                    // delete
                    if( isset( $data["delcommand"] ) && $data["delcommand"] != ""  ){
                        $id = $data["delcommand"];
                        if( isset($this->plugin->areas[$areaname]->commands[$id]) ){
                            unset($this->plugin->areas[$areaname]->commands[$id]);
                            $this->plugin->helper->saveAreas();
                            $this->areaSelectForm( $sender, Language::translate("area") . " ". $areaname . " " . Language::translate("cmd") . " " .  $id . " " .  Language::translate("ui-deleted"). " " . Language::translate("ui-select-an-option")  );
                        }else{
                            $this->areaSelectForm( $sender, Language::translate("ui-cmd-id-not-found")  );
                        }
                        if( isset($this->plugin->areas[$areaname]->events) ){
                            foreach($this->plugin->areas[$areaname]->events as $e => $i){
								$evs = explode(",", $i);
								foreach($evs as $k => $ci){
								    if($ci == $id || $ci == ''){ // also remove empty values
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

                    }else{
                        $this->areaSelectForm( $sender, Language::translate("ui-cmd-empty-not-saved")  );
                    }

                }



            });

            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["selectedArea"]];
            $area = $this->plugin->areas[$areaname]; // check is area exsists

            $form->setTitle( TextFormat::DARK_PURPLE . Language::translate("ui-edit-area-commands") ." ". Language::translate("for-area") . " " . TextFormat::DARK_PURPLE . $areaname );


            $form->addLAbel( "-------  ". Language::translate("ui-area-command-list") .": -------");

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
            $form->addLAbel( "-------- ". Language::translate("ui-area-add-command") .": --------");

            $msgdsp_tlt = Language::translate("ui-area-add-command-event");
            $msgdsp_opt = ["enter", "center", "leave"];
            $form->addStepSlider( $msgdsp_tlt, $msgdsp_opt, 0, "newcommandevent" );

            $form->addInput(Language::translate("ui-area-add-new-command"), "add new Command (without / )", "", "newcommand" );


            $form->addLAbel( "-------- ". Language::translate("ui-area-del-command") .": --------");

            $form->addInput(Language::translate("ui-area-type-command-id-del"), "input command id to delete", "", "delcommand" );


            $form->sendToPlayer($sender);


        }else{

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->areaCommandForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . Language::translate("ui-manage-area-commands"));
            if($msg){
                $form->addLabel( $msg);
            }
            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( Language::translate("ui-select-area"), $options, $slct, "selectedArea");
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
            $area = $this->plugin->areas[$areaname];

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
                        $area = $this->plugin->areas[$areaname];
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
                    $this->areaWhitelistForm( $sender, false, Language::translate("ui-area-whitelist-saved") );


                });


                $form->setTitle( TextFormat::DARK_PURPLE . Language::translate("ui-manage-area-whitelist") );

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
            $form->setTitle( TextFormat::DARK_PURPLE . Language::translate("ui-manage-area-whitelist"));
            if($msg){
                $form->addLabel( $msg);
            }else{
                $form->addLabel( Language::translate("ui-whitelist-select-area") );
            }
            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( Language::translate("ui-select-area"), $options, $slct, "selectedArea");
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
                        $sender->sendMessage(Language::translate("ui-formdate-not-available-try-again"));
                        return;
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
                            $newarea["priority"] = 0;

                            new FeArea( $newarea["name"], $newarea["desc"], $newarea["priority"], $newarea["flags"], $newarea["pos1"], $newarea["pos2"], $newarea["radius"], $newarea["level"], [], [], [], $this->plugin);
                            $this->plugin->helper->saveAreas();
                            $this->plugin->checkAreaTitles( $sender, $sender->getPosition()->getLevel() );
                            $this->areaSelectForm( $sender, Language::translate("ui-new-area-named") ." ". $newarea["name"] ." ". Language::translate("ui-created") );

                        }else{
                            $this->areaNewForm( $sender , $data, $msg = Language::translate("ui-areaname-allready-used-try-again") );
                        }
                    }
                });

                $form->setTitle( Language::translate("ui-area-maker") );
                if($msg){
                    $form->addLabel($msg);
                }else{
                    $form->addLabel(Language::translate("ui-create-area"));
                }
                $form->addInput( Language::translate("ui-area-name"), "area name", "", "name" );
                $form->addInput( Language::translate("ui-area-desc"), "area description", "", "desc" );
                $form->sendToPlayer($sender);
            }
        }else{

            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"] = [];
            // simple form select cube or sphere
            $form = new SimpleForm(function ( Player $sender, ?int $data ) {
                if( $data === null){
                    $sender->sendMessage( Language::translate("ui-formdate-not-available-try-again") );
                    return;
                }else{
                    switch ($data) {
                        case 0:
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] = "cube";
                            $o = TextFormat::GREEN . Language::translate("ui-tab-pos1-diagonal");
                            $sender->sendMessage($o);
                        break;
                        case 1:
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] = "radius";
                            $o = TextFormat::GREEN . Language::translate("ui-tab-pos1-radius");
                            $sender->sendMessage($o);
                        break;
                        case 2:
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] = "diameter";
                            $o = TextFormat::GREEN . Language::translate("ui-tab-pos1-diameter");
                            $sender->sendMessage($o);
                        break;
                        case 3:
                            $this->areaSelectForm( $sender ); // goback
                        break;
                        default:
                            $this->areaSelectForm( $sender ); // goback
                        break;
                    }
                }
            });

            $form->setTitle( Language::translate("ui-area-maker") );
            if($msg){
                $form->setContent($msg);
            }else{
                $form->setContent( Language::translate("ui-select-new-area-type") );
            }
            $form->addButton( Language::translate("ui-make-cube-diagonal") ); // cube area
            $form->addButton( Language::translate("ui-make-sphere-radius") ); // sphere area
            $form->addButton( Language::translate("ui-make-sphere-diameter") ); // sphere area
            $form->addButton( Language::translate("ui-go-back") );
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
                    $this->plugin->hideAreaTitle( $sender, $sender->getPosition()->getLevel(), $area );
                    $area->delete();
                    $this->plugin->helper->saveAreas();
                    $this->selectForm( $sender, Language::translate("area"). " ". $areaname . " ". Language::translate("ui-deleted")." ".Language::translate("ui-select-an-option") );
                }else{
                    $this->areaForm( $sender, Language::translate("area"). " ". $areaname . " ". Language::translate("ui-not-found")." ".Language::translate("ui-select-an-option") );
                }
                return false;
            });
            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["deleteArea"]];
            $form->setTitle( TextFormat::RED . Language::translate("ui-delete-this-area") . " " . TextFormat::WHITE . $areaname );
            $form->addLabel( TextFormat::RED . Language::translate("ui-gonna-delete-area") . " ".  $areaname );
            $form->sendToPlayer($sender);
        }else{
            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->areaDeleteForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . Language::translate("ui-delete-an-area"));
            if($msg){
                $form->addLabel( $msg);
            }else{
                $form->addLabel( Language::translate("ui-select-area-delete"));
            }
            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( Language::translate("ui-select-to-delete-area"), $options, $slct, "deleteArea");
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
                    $selectlist[]= $area->getName();
                }
                if(  $selectlist[ $data[0] - 1 ] ){
                    $areaname = $selectlist[ $data[0] - 1 ];
                    Server::getInstance()->dispatchCommand($sender, "fe tp ".$areaname );
                }
            }
        });

        $form->setTitle("Teleport to Area");
        $selectlist = array();
        $selectlist[] = "Select destination area";
        foreach($this->plugin->areas as $area){
            $selectlist[] = $area->getName();
        }
        $form->addDropdown("TP to area", $selectlist );
        $form->sendToPlayer($sender);
    }
}
