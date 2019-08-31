## Festival 

Create a festival with this custom area events plugin for Pocketmine Server:
###  Manage area's and run commmands attachted to area events. 
( latest stable version [@ poggit https://poggit.pmmp.io/p/Festival](https://poggit.pmmp.io/p/Festival) )

![Festival plugin logo large](https://genboy.net/wp-content/uploads/2018/02/festival_plugin_logo.png)
###### Copyright [Genboy](https://genboy.net) 2018 - 2019 
--- 
# Festival
Stable version
[![](https://poggit.pmmp.io/shield.state/Festival)](https://poggit.pmmp.io/p/Festival) [![](https://poggit.pmmp.io/shield.api/Festival)](https://poggit.pmmp.io/p/Festival) [![](https://poggit.pmmp.io/shield.dl.total/Festival)](https://poggit.pmmp.io/p/Festival) [![](https://poggit.pmmp.io/shield.dl/Festival)](https://poggit.pmmp.io/p/Festival)


[issues @ github](https://github.com/genboy/Festival/issues) and/or [reviews @ poggit](https://poggit.pmmp.io/p/Festival)


![Festival 2.0.1 Command usage](https://genboy.net/wp-content/uploads/2019/08/festival_usage_v2.0.1.png)

If you like to use Festival consider [sharing your experience and issues](https://github.com/genboy/Festival/issues) to fix any usability problems before posting a [vote](https://poggit.pmmp.io/p/Festival/1.1.1)! That way it will improve Festival, my coding skills, your Pocketmine-MP insights and strenghten the PMMP community, thank you!
 
!Take notice of the Copyright Statement if you use Festival for the first time since 27 April 2019. 
**Read the Legal Notice** at the bottom of this README file or the Legal Notice tab at poggit.pmmp.io/p/Festival


## Overview

### version 2.0.0 
> - Festival Manager Menu (UI + select item) - or use the commands
> - Cube AND Sphere area's set with diagonal, radius or diameter
> - Area's  and Config  managed from menu
> - Optional Level flag protection
> - Area name (and desc) can now be Full string inCluDing MuLti wORds CaPitaLized
> - Stretching area's up and down with y scaling
> - Use priority number for overlapping area's

**Download development version**: 
[Poggit development](https://poggit.pmmp.io/ci/genboy/Festival/Festival)
Please report bugs -thank you! [issues @ github](https://github.com/genboy/Festival/issues) and/or [reviews @ poggit](https://poggit.pmmp.io/p/Festival)

or use [devtools plugin](https://poggit.pmmp.io/p/DevTools/1.13.0) and [download zip package https://github.com/genboy/Festival/archive/master.zip](https://github.com/genboy/Festival/archive/master.zip)

**Festival version 2.0.0 Install**: 
*(always save copies of your previous used config.yml and areas.json before re-install)*
1. place phar file or unzipped Festival folder with (Devtools pluginfolder) in server plugins folder and restart, 
2. after restart;
 2a. if need previous used configs and areas: delete config.json and areas.json from the root folder 
and put your config.yml and areas.json in Festival (root) folder
 2b. if clean start (no areas)  edit /resources/config.yml to your likes and delete config.json from the root folder
3. Then restart again, now areas.json, levels.json and config.json in Festival (root) folder are used.

( or download latest stable version [@ poggit https://poggit.pmmp.io/p/Festival](https://poggit.pmmp.io/p/Festival) - no Festival menu, only command usage)

**Management UI in game**: 
command **hold magic item** or ** /fe menu** (ui, form, data)
( default magic item 201 - Purpur Pillar block - change in config management) 
When using the form you need to **use the magic item to tab area positions**.
You can swapp item to build/break during area position 1 and 2 selection.

Or use the commands as shown in the usage image (now with Multi wORd FULLY CapitAlized nameS possible) 

  **Create area** with **menu**:  
 
    You can use cmd '/fe menu' or just hold the magic item (purpur block/your selected item/block). Then if you choose create area you should tab the positions with the magic item.  
    After tab pos1 you may use other blocks to build etc. and then hold the magic item again to tab pos2. Directly after tab pos2 the menu should come back to name your area.

 **Create area** with **commands**:  
    
    Use /fe pos1 and /fe pos2 to tab the positions, after pos2 you need '/fe create area ' to finnish the area creation.  
    
Here after both commands and menu can be used to manage the area.  

---
## Info
### Features


**Menu**
In version 2.0.0 the Festival Management Menu (FormUI) is introduced

**Config**
- set default options in config.yml;
  - Language: en - select language English = en, Dutch = nl, es = Español, pl = Polskie - translate please !
  - ItemID: Hold this Magic block/item to enter Menu (default item 201 - Purpur Pillar block)
  - Msgtype: msg - Area Messages Display position (msg/title/tip/pop)
  - Msgdisplay: off - Area Messages persist display to ops (off/op/on)
  - Areadisplay: op - Area Floating Title display to ops (off/op/on)
  - FlightControl: on - To disable flight flag for all Festival usage (on/off)
  - AutoWhitelist: on - Auto whitelist area creator (on/off)

**Area**

- Create and manage area’s ingame

    - Define area's by tapping 2 positions
      - **diagonal** for cube
      - **radius** for sphere
      - **diameter** for sphere
    - Scale area's verticaly up and down 
    - create/rename/delete/list area’s
    - add area description
    - whitelist players for the area
    - tp to an area
    - show area’s info at current position 

**Flags**

- Set area flags ingame 
  Flags: Any flag true will protect the area and the players in it. 
  ie. edit: true (on) means no breaking/building by players. shoot: true (on) means no shooting by players.
  
    - edit: the area is save from building/breaking
    - hurt: players in the area are save (previous god flag)
    - pvp: players in the area are save from PVP
    - flight: players in the area are not allowed to fly
    - touch: area is save from player interaction with doors/chests/signs etc.
    - animals: no animal spawning (including spawners & eggs)
    - mobs: no mobs spawning (including spawners & eggs)
    - effect: player can not keep using effects in the area
    - msg: do not display area enter/leave messages
    - pass: no passage for non-whitelisted players! (previously barrier flag)
    - drop: players can not drop things
    - tnt: explosions protected area
    - fire: fire protected area (including spreading & lava)
    - explode: explosions protected area
    - shoot: player can not shoot (bow)
    - perms: player permissions are used to determine area command execution
    - hunger: player does not exhaust / hunger
    - fall: player will not have fall damage
    - cmd: area event commands are only executed for ops (test area commands)


**Events & Commands**

- Add commands to area events

    - assign commands to area events
    - enter, center or leave.
    - variable player in commands with {player} or @p
    - add/edit/delete area event command
    - list area commands (ordered by event)
    - change event of area commands 


**Level flags**

- Use the level flags for level protection

    - per level the levelcontrol toggle option enables the level flag protection
---


## Menu (UI)

#### Festival Menu

**Festival main menu**

![Start menu select management option](https://genboy.net/wp-content/uploads/2019/06/manager_start.jpg)
#### Teleport

**Select teleport destination**

![Select teleport destination](https://genboy.net/wp-content/uploads/2019/06/area_teleport_select.jpg)

#### Areas

**Area management option menu**

![Area option menu](https://genboy.net/wp-content/uploads/2019/06/manager_area_options.jpg)

**Select area to manage**

![Select area](https://genboy.net/wp-content/uploads/2019/06/manager_area_select.jpg)

**Manage area settings**

![Edit area settings](https://genboy.net/wp-content/uploads/2019/06/manage_areas_settings.jpg)

**Manage area flags**

![Edit area flags](https://genboy.net/wp-content/uploads/2019/06/manager_area_options_end.jpg)

**Manage area commands**

![Manage commands for area events](https://genboy.net/wp-content/uploads/2019/07/cmds_1_Minecraft-27-1-2019-16_50_24.jpg)


**Add command**

![Edit or add commands to area](https://genboy.net/wp-content/uploads/2019/07/cmds_2.jpg)

using the @p reference in the command to target the player 
(/heal command is an example, comes from another plugin)


**Del or change command** (by id) 

![Edit or add commands to area](https://genboy.net/wp-content/uploads/2019/07/cmds_3.jpg)

Delete: Leave command empty and input 'delete cmd id' to delete id linked command.
Change: Set event type, enter command and input 'edit cmd id' to change that id linked command


**Manage area Whitelist**

![Manage area whitelist](https://genboy.net/wp-content/uploads/2019/06/area_whitelist.jpg)

Set players on or off area whitelist (this is in development)


**Select area to delete**

![Delete area](https://genboy.net/wp-content/uploads/2019/06/delete_area1.jpg)

**Confirm to delete area**

![Cofirm area delete](https://genboy.net/wp-content/uploads/2019/06/delete_area2.jpg)

#### Create

Select new area type

![Select new area type](https://genboy.net/wp-content/uploads/2019/06/start_make_area.jpg)
Hold the magic item - 201 purpur block by defaults, set in configs
**Tab positions with the magic block**, meanwhile use other blocks to build in between.

Set area positions
###### Cube Diagonal 
    1. Place or break the first diagonal position for new cube area
    2. Place or break position 2 to set the longest diagonal in the new cube area
###### Sphere Radius 
    1. Place or break the center position for the new sphere area
    2. Place or break position 2 to set the radius for new sphere area
###### Sphere Diameter
    1. Place or break the first diameter position for the new sphere area
    2. Place or break position 2 to set the diameter for new sphere area
 
Create area with Name (and description)

![Create area with name and description](https://genboy.net/wp-content/uploads/2019/06/create_new_area.jpg)

#### Levels

Turn on level flag control to use the level flags (instead of defaults)
![festival-use-level-flags](https://user-images.githubusercontent.com/30810841/63712056-291f7b00-c83d-11e9-9209-2dbb66c163fe.gif)

Select level to manage flags (if levelcontrol config is on)

![Select level](https://genboy.net/wp-content/uploads/2019/06/manage_level_select.jpg)

Manage level flags options

![Manage Level use option and flags](https://genboy.net/wp-content/uploads/2019/07/manage_level_option.jpg)
![Edit level flags(defaults)](https://genboy.net/wp-content/uploads/2019/06/manage_level_flags2.jpg)

#### Configuration
UI configuration to set overall options and default flags for levels and area's
![festival-set-configs-default-flags](https://user-images.githubusercontent.com/30810841/63712052-26bd2100-c83d-11e9-82f0-5ed7f03312f4.gif)


Manage Festival configuration options and set default flags 

![Manage configuration](https://genboy.net/wp-content/uploads/2019/06/manager_configuration.png)
###### Copyright [Genboy](https://genboy.net) 2018 - 2019 - markdown edited with [stackedit.io]

## Usage 
  
  - Edit config.yml; set the defaults for options, default area flags and the default area flags for specific worlds.
  - using ingame Festival Menu (UI) for configurations
  - older versions (1.1.3) read [wiki on configurations](https://github.com/genboy/Festival/wiki/2.-Install,-Configure-&-Update)

![Festival 2.0.1 Command usage](https://genboy.net/wp-content/uploads/2019/08/festival_usage_v2.0.1.png) 


#### Setup

### Install & Configure

  - Standard Plugin installation; Upload .phar file to server 'plugin' folder (or upload .zip if you have latest devtools installed), restart the server, go to  folder plugins/Festival;

  - read [wiki on configurations](https://github.com/genboy/Festival/wiki/2.-Install,-Configure-&-Update)
  
#### Updates
  
  Updates available at [poggit](https://poggit.pmmp.io/ci/genboy/Festival/Festival) and [github](https://github.com/genboy/Festival/releases)
  
##### !Before update always copy your config.yml and areas.json files to a save place, with this you can revert your Festival installation. Keep your old files (befor v2.0.0) for new install.
  - first remove Festival folder (keep the areas.json and config.yml)
  - after .phar install and first restart/reload plugins; check console info
  - replace the new(empty) areas.json with your original (old) areas.json
  - put your original config.yml in the Festival (or /resource) folder and remove the config.json file; 
  - restart server after adjusted correctly
  
  ###### Copyright [Genboy](https://genboy.net) 2018
  
  
#### Festival Manager menu

  Open th Festival menu
    
    /fe ui
    /fe menu
    
  or get hold of the magic item in the inventory

#### Language
  
    /fe lang <en/nl/es/pl>
  
  Set Festival language en/nl/es/pl for area and command returned messages. 
  en = English
  nl = Nederlands 
  es = Espanol 
  pl = Polski
  __ = your language, please help [translate](https://github.com/genboy/Festival/tree/Translations)
  

#### Create area (cmd)
  
    ### Cube area
  
    First command  '/fe pos' or '/fe pos1' 
    and holding the magic block, default 201, tab or break a block for position 1 
  
    then command '/fe pos2' 
    and and holding the magic block tab or break a block to set position2, 
  
    these are the endpoints of the area longest diagonal.

	/fe pos1(pos)
	/fe pos2
    
    
  ### Sphere area
  
    First command '/fe pos' or '/fe pos1'
  
    For sphere radius;
    holding the magic block tab or break a block for the center of the sphere  
    then command '/fe rad' or '/fe radius'
    and and holding the magic block tab or break a block to set the radius size.
  
    For sphere diameter;
    holding the magic block tab or break a block for first end of the diameter
    then command '/fe dia' or '/fe diameter'
    and and holding the magic block tab or break a block for the other end of the diameter.

	/fe pos
	/fe rad / dia


  ### After position selections

    Then name/save the selected area

	/fe create <AREANAME>  

  Now the area is ready to use
  
  You might want to set or edit the area description line
   
    /fe desc <AREANAME> <description>


#### Set area flags 
  
    fast toggle for flags: (since Festival v1.0.1-11)
  
      /fe <edit/hurt/pvp/flight/touch/mobs/animals/effect/tnt/fire/explode/shoot/drop/msg/pass/hunger/perms/fall/cmd> <AREANAME>

    Area flag defaults are set in the config.yml, server defaults and world specific default flag. 
    
      /fe flag <AREANAME> list
      
  
#### Delete an area
	
	  /fe delete(del,remove) <AREANAME>   
    
  
#### List all area's
	
    See all area info, optional per level
    
      /fe list (<LEVELNAME>)

  
#### Floating titles

    Floating titles are set in the configs (menu or config.json / yml)
    Toggles the titles on/off
  
    /fe titles

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

### Updates
  
 Updates available at [poggit](https://poggit.pmmp.io/ci/genboy/Festival/Festival) and [github](https://github.com/genboy/Festival/releases)
  
  ##### !Before update always copy your config.yml and areas.json files to a save place, with this you can revert your Festival installation
  - after .phar install and first restart/reload plugins; check console info and your areas.json and config.yml; restart after adjusted correctly 
  
  - ! Update Festival 2 in development translating resource config.yml or your mainfolder config.yml and areas.json on install
  

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
###### Copyright [Genboy](https://genboy.net) 2018 - 2019 
markdown edited with [stackedit.io](https://stackedit.io) and  
translated to html with [browserling.com](https://www.browserling.com/tools/markdown-to-html)
