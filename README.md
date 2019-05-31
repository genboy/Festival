
## Festival

If you like to use Festival consider [sharing your experience and issues](https://github.com/genboy/Festival/issues) to fix any usability problems before posting a [vote](https://poggit.pmmp.io/p/Festival/1.1.1)!
That way it will improve Festival, my coding skills, your Pocketmine-MP insights and strenghten the PMMP community, thank you!
 
![Festival plugin logo large](https://genboy.net/wp-content/uploads/2018/02/festival_plugin_logo.png) 


!Take notice of the Copyright Statement if you use Festival for the first time since 27 April 2019. 
Read the Legal Notice at the bottom of this README file or the Legal Notice tab at poggit.pmmp.io/p/Festival

Create a festival with this custom area events plugin for Pocketmine Server:

### Manage area's and run commmands attachted to area events. 

 
![Festival creation & usage](https://genboy.net/wp-content/uploads/2019/04/festival_usage_v1.1.3.png)  


###### Copyright [Genboy](https://genboy.net) 2018 
 
--- 

## Info
Since Festival 1.1.3 the config option to disable Flight flag (flight control off) is available!
Festival 2 is being implemented step by step now, [development](https://github.com/genboy/Festival/projects/2) including FormUI for easy control, a new code structure and some of the flags will be completely rewritten. Just to let you know ..

# Festival

[![](https://poggit.pmmp.io/shield.state/Festival)](https://poggit.pmmp.io/p/Festival) 
[![](https://poggit.pmmp.io/shield.api/Festival)](https://poggit.pmmp.io/p/Festival)
[![](https://poggit.pmmp.io/shield.dl.total/Festival)](https://poggit.pmmp.io/p/Festival)
[![](https://poggit.pmmp.io/shield.dl/Festival)](https://poggit.pmmp.io/p/Festival)

[issues @ github](https://github.com/genboy/Festival/issues) and/or [reviews @ poggit](https://poggit.pmmp.io/p/Festival)
	
More info also available at [the Festival Wiki](https://github.com/genboy/Festival/wiki) 

Download the latest .phar files from [poggit.pmmp.io](https://poggit.pmmp.io/p/Festival/1.1.3), also available at [mcpehost.ru](https://panel.mcpehost.ru/repository/plugin?name=Festival)

!Please before asking; first double-check your server basic world configurations, other plugins configurations (ie. worldguard) and the used player permissions incl. Festival whitelistings.

#### Features

**Config**
- set default options in config.yml;
  - language: en - select language English = en, Dutch = nl, es = Español, pl = Polskie - translate please !
  - Msgtype: msg - Area Messages Display position (msg/title/tip/pop)
  - Msgdisplay: off - Area Messages persist display to ops (off/op/on)
  - Areadisplay: op - Area Floating Title display to ops (off/op/on)
  - FlightControl: on - To disable flight flag for all Festival usage (on/off)
  - AutoWhitelist: on - Auto whitelist area creator (on/off)
  
**Area**

- Create and manage area’s ingame
  (like WorldGuard/iProtector)

    - Define cuboid area by tapping 2 positions
    - create/delete/list area’s
    - add area description
    - whitelist players for the area
    - tp to an area
    - show area’s info at current position 

**Flags**

- Set area flags ingame 
  Flags: Any flag true will protect the area and the players in it. 
  ie. edit: true (on) means no breaking/building by players. shoot: true (on) means no shooting by players.
  
    - edit: the area is save from building/breaking
    - god: players in the area are save in god mode
    - pvp: players in the area are save from PVP
    - flight: players in the area are not allowed to fly
    - touch: area is save from player interaction with doors/chests/signs etc.
    - animals: no animal spawning (including spawners & eggs)
    - mobs: no mobs spawning (including spawners & eggs)
    - effects: player can not keep using effects in the area
    - msg: do not display area enter/leave messages
    - passage: no passage for non-whitelisted players! (previously barrier flag)
    - drop: players can not drop things
    - tnt: explosions protected area
    - fire: fire protected area (including spreading & lava)
    - explode: explosions protected area
    - shoot: player can not shoot (bow)
    - perms: player permissions are used to determine area command execution
    - hunger: player does not exhaust / hunger
    - falldamage: player will not have fall damage (no fall damage)
    - cmdmode: area event commands are only executed for ops (test area commands)


**Events & Commands**

- Add commands to area events

    - assign commands to area events
    - enter, center or leave.
    - variable player in commands with {player} or @p
    - add/edit/delete area event command
    - list area commands (ordered by event)
    - change event of area commands 


**Specific**

  - World flags: Default & level(world) specific flags in config.yml are used for level default flag settings and new area flags settings
  - Flight: if server allows flight, and level flight-flag is true, an area in that level has still flight enabled untill flight flag is set true
  - Perms: Area event commands are executed by default with op-permissions by players or, if perms flag true: area uses the player permissions
  - Area Titles: Set area titles to display, for ops or any player (in config.yml), ops can select display ingame with /fe titles
  - CMDmode: The cmdmode flag disables event commands for (whitelisted)players, allows ops to test area event commands.
	
!Please first check festival and other plugins configs (ie. worldguard) and the used player permissions incl. Festival whitelistings.

  

###### Created by [Genboy](https://genboy.net) 2018

Credits for the area creation and protection code go to iProtector creator [LDX-MCPE](https://github.com/LDX-MCPE) 
and all [other iProtector devs](https://github.com/LDX-MCPE/iProtector/network).

---

## Usage 


  #### Setup

  ### Install & Configure

  - Standard Plugin installation; Upload .phar file to server 'plugin' folder (or upload .zip if you have latest devtools installed), restart the server, go to  folder plugins/Festival;

  - read [wiki on configurations](https://github.com/genboy/Festival/wiki/2.-Install,-Configure-&-Update)

  - Edit config.yml; set the defaults for options, default area flags and the default area flags for specific worlds.
  
  - ##### Read the config comments carefully about how the flags work!
  
  
  ### Updates
  
  Updates available at [poggit](https://poggit.pmmp.io/ci/genboy/Festival/Festival) and [github](https://github.com/genboy/Festival/releases)
  
  ##### !Before update always copy your config.yml and areas.json files to a save place, with this you can revert your Festival installation
  - after .phar install and first restart/reload plugins; check console info and your areas.json and config.yml; restart after adjusted correctly 
  

  

  #### Usage Graphic

  ##### A visualisation of Festival command usage
  
  ![Festival creation & usage](https://genboy.net/wp-content/uploads/2019/04/festival_usage_v1.1.3.png) 
  
  ###### Copyright [Genboy](https://genboy.net) 2018
  

 
  #### Language
  
  Set Festival language en/nl/es/pl for area and command returned messages. 
  en = english
  nl = nederlands 
  es = Espanol
  pl = Polski
  __ = your language, please help [translate __.js](https://github.com/genboy/Festival/blob/master/resources/en.json)
  

  #### Create area
  
  First command '/fe pos1' and tab or break a block for position 1 (holding a block, not an item), 
  
  then command '/fe pos2' and tab or break a block to set position2, 
  
  these are the endpoints of the area longest diagonal.

	/fe pos1
	/fe pos2


  Then name/save the selected area

	/fe create <AREANAME>  

  Now the area is ready to use
  
  You might want to set or edit the area description line
   
    /fe desc <AREANAME> <description>


  #### Set area flags 
  
    Festival v1.0.1-11 introduced a fast toggle for flags:
  
      /fe <edit/god/pvp/flight/touch/mobs/animals/effects/tnt/fire/explode/shoot/drop/msg/pass/hunger/perms/falldamage/cmdmode> <AREANAME>

  
    Area flag defaults are set in the config.yml, server defaults and world specific default flag. 
    
    
    The basic command to control area flags:
  
	  /fe flag(f) <AREANAME> <edit/god/pvp/flight/touch/mobs/animals/effects/tnt/fire/explode/shoot/drop/msg/pass/hunger/perms/falldamage/cmdmode> <true/false>
  
    Area flag listing
  
      /fe flag <AREANAME> list
      
  
  #### Delete an area
	
	/fe delete(del,remove) <AREANAME>   
    
  
  #### Position info
	
    See area information at position
    
	/fe here


  #### List all area's
	
    See all area info, optional per level
    
	/fe list (<LEVELNAME>)


  #### Teleport to area
	
    Teleporting to area center top, drop with no falldamage (if falldamage flag true)
    
	/fe tp <AREANAME>


  #### Toggle level area's floating title display
	
    Area floating title display (default set in config.yml)
    
	/fe titles


  #### Set description
		
	/fe desc <AREANAME> <DESCRIPTION>

  
  #### Manage  whitelist
	
    /fe whitelist <AREANAME> <add/list/remove(del,delete)> <PLAYERNAME>


  #### Area event commands

    **This is the fun part of Festival: assign commands to area events**
    
    When an area is created 3 events are available;
      - enter; when a player enters the area
      - center; when a player reaches the center (3x3xareaHeight blocks)
      - leave; when a player leaves the area


    To add a command you need at least;
      - an areaname, 
      - an unique id for the command 
      - make sure the command works! (when you are op)
      
      
    /fe command <AREANAME> <add/list/edit/event*/del> <COMMANDID> <COMMANDSTRING/enter*/leave*/center*> 
 
	
    
    
  #### Add a command:

	/fe command <AREANAME> add <COMMANDID> <COMMANDSTRING>

 	  'add' is the default for attaching a command on the 'enter' event. 
	  Using 'enter', 'center' or 'leave' instead of 'add' attaches the new command to 
	  the given eventtype: i.e. /fe command <areaname> center <commandid> <commandstring>

  #### List area commands:
	
	/fe command <AREANAME> list
		
  #### Edit command:
	
	/fe command <AREANAME> edit <COMMANDID> <COMMANDSTRING>
	
  #### Change command event:
	 
	/fe command <AREANAME> event <COMMANDID> <enter/center/leave>
		
  #### Remove command:
	
	/fe command <AREANAME> del <COMMANDID>

---

## Development

The Festival plugin is in active development.

 ##### Development on [github.com/genboy/Festival](https://github.com/genboy/Festival)

If you like to help improve this plugin;

- download/use the plugin and give your feedback
- look at the code and give feedback
- help translating [__.js](https://github.com/genboy/Festival/blob/master/resources/en.json)
- both by submitting [issues @ github](https://github.com/genboy/Festival/issues) and/or [reviews @ poggit](https://poggit.pmmp.io/p/Festival) 

or send an email to msg @ genboy.net 
	
Thank you


### Milestones v1.0.0-11 - v1.0.3-11

  - [x] area protection and flag management is stable 
  (core [iProtector](https://github.com/poggit-orphanage/iProtector), [9876ca3](https://github.com/poggit-orphanage/iProtector/commit/9876ca3acd48830599b3715346a1cf8ac964bdbd) Dec 2017) 

  - [x] Area messages and msg/description management are stable
  - [x] Commands can be attachted to specific events at the area: 
  - [x] enter: on entering the area
  - [x] center: when in the center of the area
  - [x] leave: when leaving the area 
  - [x] Submit to poggit
  - [x] Testing expected possibilities; use as portals and shields, design a minigame park, create a quest/parcour.. 
  - [x] Passage flag; turning the area into a barrier, no one in, no one out.
  - [x] /fe tp <areaname> now sends player to the area top-center and prevents fall damage  
   
### Milestones v1.0.3-11 - v1.0.7

  - [x] Config options:
    - Messages out of chat (tip/pop)  
    - Messages persist display to ops (off/op/on)
    - Auto whitelist area creator (on/off)
  - [x] Effects flag: remove players effects in area
  - [x] Perms flag: player perms used for area commands (vs OP pems)
  - [x] Drop flag: player can not drop things 
  - [x] PVP flag: players can not PvP (warn message)
  - [x] Flight flag: players can not fly (incl. no fall damage & allowed messages)
  - [x] Area Commands: playerName can be used as **{player}** or **@p** in area event commands
  - [x] TNT flag: explosion free area's
  - [x] Hunger flag: players do not exhaust 
  - [x] Fire (animation) extinguished when player is save 
  - [x] No shooting (bow)
  - [x] No Fall Damage flag (was implemented as effect for TP dropping and flight break)
 
### Milestones v1.0.7 - v1.0.7.9

  - [x] Areas floating title
    - set config option Areadisplay (off/op/on)
    - never or on command (off)
    - for ops only always (op)
    - for all players if msg flag true (on)
    - display toggle /fe titles for ops
  - [x] Add translation options
    - set config option language (en/nl/..)
    - ops change language  /fe lang <en/nl/..>
    - English en
    - Nederlands nl
  - [x] Enhancements Edit Flag 
    - No Farmland creation
    - No Fire from Flint & Steel 
    - protect item in frame use
  - [x] Enhancements TNT Flag (experimental)
    - No TNT placing
    - No TNT ignition with Flint & Steel
  - [x] Spawning: Prevent mob spawning (and spawners/eggs) in area's 
    - Prevent spawn (incl. spawners/eggs) (EntitySpawnEvent)!
      - Mobs flag prevent mobs from spawning in area
      - Animals flag prevent animals from spawning in area
  - [x] Area messages display in chat with config option Msgtype 'msg' 
  - [x] cmd flag: area event commands for ops or whitelisted players only
 
 
 
 ### History
  
  **Since v1.0.3-11+**
  
  - pass(passage) flag gives the area a barrier for non ops/whitelisted
  - configuration for area messages (taken out of chat)
    - Msgtype: tip or pop (prefer depend on other plugin message display) 
    - Msgdisplay: 
        off = hidden for all players
        op = only ops see all area enter/leave messages
        on = all players see the area messages
  
  **Since v1.0.4-11**

  - areas are updated with the new flags, configuration should be updated manually; example [resources/config.yml](https://github.com/genboy/Festival/blob/master/resources/config.yml)

  **Since v1.0.5-12**

  - configuration should be updated with AutoWhitelist option & new Effects flag; example [resources/config.yml](https://github.com/genboy/Festival/blob/master/resources/config.yml)

  **Since v1.0.6-13**

  - new PVP flag
  - new Flight flag
  - /fe list LEVELNAME - Area list of all area's in all levels, or for specified level 
  - configuration should be updated [resources/config.yml](https://github.com/genboy/Festival/blob/master/resources/config.yml)
 
  **Since v1.0.7**
  - new TNT flag
  - new Hunger flag
  - Fire is now extinguished when player does not get damage (aka. in area with god flag on)
  - new shoot flag (experimental no shooting/launching)
  - new falldamage flag
  - new animals and mobs (spawning) flag 
  - Fixes itemframe and farmland edit  

  **Since v1.0.8**
  - /fe lang <en/nl/..> - set  Festival language
  - Edit flag includes No Farmland creation
  - Edit flag includes protect item in frame use
  - !NEW Fire flag includes No Fire from Flint & Steel and No lava 
  - !NEW TNT flag includes No TNT placing or tnt explosions 
  - !NEW Explode flag includes No entity explosions  
  - Areas floating title
  - Add translation options (en/nl/..)
  - Spawning: Prevent mob/animal spawning (and spawners/eggs) in area's 
  - Mobs flag prevent mobs from spawning in area
  - Animals flag prevent animals from spawning in area
  - Area messages display in chat with config option Msgtype 'msg' 
  - cmd flag: area event commands for ops or whitelisted players only 

  **Since v1.0.9**
  - gmc flying allowed by default
  - fix inArea Player availabillity
  - fix player damage cause check
    
  **Since v1.1.0**
  - fix griefing bug: adjusted in some functions to determine the needed position to validate the action(flag).
      
  **Since v1.1.2**
  - fix translation bug: adjusted utf-8 json encoding, 
  - Thanks to [@bptube](https://github.com/bptube) now including Spanish language! 
  
  **Since v1.1.1**
  - fix transalation and mobs/animals error + @bptube added & reviewed Español(/fe lang es)
  
  **Since v1.1.2**
  - new pl.json and @dearminder reviewed Polskie(/fe lang pl)
  
  **Since v1.1.3**
  - new class ForceUTF8 for json encoding translations
  - new FlightControl option to disable flight flag
  
  **Since v1.1.4**
  - Sphere shape area /fe pos1 + /fe radius 
  
------ 
 

## Credits

The area code derives from the [iProctector plugin](https://github.com/LDX-MCPE/iProtector). All credits for the area creation and protection code go to the iProtector creator [LDX-MCPE](https://github.com/LDX-MCPE) and [other iProtector devs](https://github.com/LDX-MCPE/iProtector/network).

The Festival code is written and tested by Genboy and first released on 12 Feb 2018, first extending the area object with area events (enter and leave messages) and soon added functions and ingame commands to attach a commandstring to a area-event. Since v1.0.7 the area's and players can be protected with 12 flags, and trigger commands on areaEnter, areaCenter and areaLeave. 

## Legal Notice

-- Legal notice --

For Festival the General Public License agreement version 3, as in the LICENSE file is still included and operative.

To protect this software since 27 April 2019 the Festival software package is copyrighted by Genboy. 
You are legally bind to read the Festival Copyright statement. 

In short this change of Copyright statement does not change the usage levels as stated in the GPU, for a part it now prohibits any entities to sell the software without the knowledge of the owner. 

-- end legal notice -- 