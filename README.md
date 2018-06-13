## Festival

If you like Festival please leave a thumb up at [poggit](https://poggit.pmmp.io/p/Festival/1.0.6-13)  to help getting the Festival plugin approved, thank you!_

![Festival plugin logo large](https://genboy.net/wp-content/uploads/2018/02/festival_plugin_logo.png)


Create a festival with this custom area events plugin for Pocketmine Server ALPHA10+:

### Manage area's and run commmands attachted to area events.


![Festival creation & usage](https://genboy.net/wp-content/uploads/2018/06/festival_usage_1.0.6-13x.png)


###### Copyright [Genboy](https://genboy.net) 2018
 
---

## Info 
 
# Festival

[![](https://poggit.pmmp.io/shield.state/Festival)](https://poggit.pmmp.io/p/Festival)
[![](https://poggit.pmmp.io/shield.api/Festival)](https://poggit.pmmp.io/p/Festival)
[![](https://poggit.pmmp.io/shield.dl.total/Festival)](https://poggit.pmmp.io/p/Festival)
[![](https://poggit.pmmp.io/shield.dl/Festival)](https://poggit.pmmp.io/p/Festival)

[issues @ github](https://github.com/genboy/Festival/issues) and/or [reviews @ poggit](https://poggit.pmmp.io/p/Festival)

#### Features

**Area**

- Create and manage area’s
  (like WorldGuard/iProtector)

    - Define cuboid area by tapping 2 positions
    - create/delete/list area’s
    - add area description
    - whitelist players for the area
    - tp to an area
    - show area’s info at current position


**Flags**

- Set area flags true means

    - edit: area is save from building/breaking
    - god: players in the area are save in god mode
    - pvp: players in the area are save from PVP
    - flight: players in the area are not allowed to fly
    - touch: area is save from player interaction with chests/signs etc.
    - effects: player can not keep using effects in the area (v.1.0.5-12)
    - msg: do not display area enter/leave messages 
    - passage: no passage for non-whitelisted players! (previously barrier flag)
    - perms: player permissions are used to determine area command execution (experiment)
    - drop: players can not drop things


**Events & Commands**

- Add commands to area events

    - assign commands to area events
    - enter, center or leave.
    - add/edit/delete area event commands
    - list area commands (ordered by event)
    - change event of area commands


**Specific**

  - World: Default & world specific flags in config.yml
  - Flight: if server allows flight, and level flight-flag is true, an area in that level has still flight enabled untill flight flag is set true
  - Perms: Area event commands are executed by default with op-permissions by players from the area. In v1.0.4-11 an experimental perms flag is added, perms flag true: area uses the player permissions
	

###### Created by [Genboy](https://genboy.net) 2018

Credits for the area creation and protection code go to iProtector creator [LDX-MCPE](https://github.com/LDX-MCPE) 
and all [other iProtector devs](https://github.com/LDX-MCPE/iProtector/network).

---

## Usage 


  #### Setup

  ### Install & Configure

  - Standard Plugin installation; Upload .phar file (or .zip if you have latest devtools installed), restart the server, go to  folder plugins/Festival;

  - Edit config.yml; set the defaults for options, default area flags and the default area flags for specific worlds.
  
  - ##### Read the config comments carefully about how the flags work!
  
  
  ### Updates
  
  
  
  ##### !Before update always copy your config.yml and areas.json files to a save place, with this you can revert your Festival installation
  - after .phar install and first restart/reload plugins; check console info and your areas.json and config.yml; restart after adjusted correctly 
  
  **Since v1.0.3-11+**
  
  - pass(passage) flag replaces the barrier flag
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
 
	
  #### Create area
  First command '/fe pos1' and tab a block for position 1, 
  then command '/fe pos2' and ab a block to set position2, 
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
  
      /fe <edit/god/pvp/flight/touch/effects/drop/msg/pass/perms> <AREANAME>

  
    Area flag defaults are set in the config.yml), server defaults and world specific default flag. 
    The basic command to control area flags:
  
	  /fe flag(f) <AREANAME> <edit/god/pvp/flight/touch/effects/drop/msg/pass/perms> <true/false>
  
    Area flag listing
  
      /fe flag <AREANAME> list
  
  
  #### Position info
	
	/fe here

  #### List all area's
	
	/fe list

  #### Teleport to area
	
	/fe tp <AREANAME>
  
  #### Set description
		
	/fe desc <AREANAME> <DESCRIPTION>
  
  #### Manage  whitelist
	
    /fe whitelist <AREANAME> <add/list/remove(del,delete)> <PLAYERNAME>
  
  #### Delete an area
	
	/fe delete(del,remove) <AREANAME> 

  #### Area event commands

    /fe command <AREANAME> <add/list/edit/event*/del> <COMMANDID> <COMMANDSTRING/enter*/leave*/center*> 
 
    To add a command you need at least;
      - an areaname, 
      - an unique id for the command 
      - make sure the command works! (when you are op). 
	
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
   
### Milestones v1.0.3-11 - v1.0.6-13

  - [x] Config options:
    - Messages out of chat (tip/pop)  
    - Messages persist display to ops (off/op/on)
    - Auto whitelist area creator (on/off)
  - [x] Effects flag: remove players effects in area
  - [x] Perms flag: player perms used for area commands (vs OP pems) [experimental]
  - [x] Drop flag: player can not drop things 
  - [x] PVP flag: players can not PvP (warn message)
  - [x] Flight flag: players can not fly (incl. no fall damage & allowed messages)
  - [x] Area Commands: playerName can be used as **{player}** or **@p** in area event commands
  
  
------


## Credits

The area code derives from the [iProctector plugin](https://github.com/LDX-MCPE/iProtector). All credits for the area creation and protection code go to the iProtector creator [LDX-MCPE](https://github.com/LDX-MCPE) and [other iProtector devs](https://github.com/LDX-MCPE/iProtector/network).

In a first fork from [poggit-orphanage](https://github.com/poggit-orphanage/iProtector) the new code was extending the area with enter and leave messages and adding options to attach separate event-objects to an area and trigger specific events with commands. These test versions kept the core iProtector areas unchanged (to be able to use excisting area's). 

These first adjustments worked well being a test plugin but keeping iProtector area's while adding separate event data made me create a split command structure (wich isn't logical or handy) and separate event objects are only needed if the original area class should stay the same. So, for a better plugin command structure and performance the iProtector Area code was used to create the setup for what now has become the Festival Plugin.

