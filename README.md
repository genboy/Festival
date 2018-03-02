## Overview

![Festival plugin logo large](https://genboy.net/wp-content/uploads/2018/02/festival_plugin_logo.png)


Create a festival with this custom area events plugin for Pocketmine Server ALPHA10+:

### Manage area's and run commmands attachted to area events.


![Festival command usage overview](https://genboy.net/wp-content/uploads/2018/02/festival_usage_1.0.2-11.png)

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

Area

- Create and manage area’s
  (like WorldGuard/iProtector)

    - Define cuboid area by tapping 2 positions
    - create/delete/list area’s
    - add area description
    - whitelist players for the area
    - tp to an area
    - show area’s info at current position


Flags

- Set flags true for players not to

    - edit: disallow building/breaking
    - god: deny players god mode
    - touch: disallow interaction with chests etc.
    - msg: hide enter/leave messages
    - passage: no passage for non-whitelisted players! (previously barrier flag)

    Default & world specific flags in config.yml


Events

- Add commands to area events

    - assign commands to area events
    - enter, center or leave.
    - add/edit/delete area event commands
    - list area commands (ordered by event)
    - change event of area commands

    Note! Area event commands are executed with op permission by players from the area.


###### Created by [Genboy](https://genboy.net) 2018

Credits for the area creation and protection code go to iProtector creator [LDX-MCPE](https://github.com/LDX-MCPE) and all [other iProtector devs](https://github.com/LDX-MCPE/iProtector/network).

---

## Usage 

### Install & Configure

  - Standard Plugin installation; Upload .phar file (or .zip if you have latest devtools installed), restart the server, go to  folder plugins/Festival;

  - Edit config.yml; set the defaults for any area and the defaults for area's in specified worlds.
  - ##### Read the config comments carefully about how the flags work!

	
  #### Define area
  first select position 1, then select position2, 
  the endpoints of the longest diagonal in the area

	/fe pos1
	/fe pos2

  #### name/save the selected area

	/fe create <AREANAME>  

  #### set the area flags (!defaults in config.yml)
	
	/fe flag(f) <AREANAME> <god/build/touch/msg/pass> <true/false>
		
  #### fast flag toggle (new! v1.0.1-11)
		
 	/fe <god/build/touch/msg/pass> <AREANAME> (<true/false>)
		
  
  #### see info on the area's you're in
	
	/fe here

  #### see a list of all area's
	
	/fe list

  #### teleport to an area
	
	/fe tp <AREANAME>
  
  #### set area description
		
	/fe desc <AREANAME> <DESCRIPTION>
  
  #### manage players in area whitelist
	
    /fe whitelist <AREANAME> <add/list/remove(del,delete)> <PLAYERNAME>
  
  #### delete an area
	
	/fe delete(del,remove) <AREANAME> 

  #### command to attach commands to an area

    /fe command <AREANAME> <add/list/edit/event*/del> <COMMANDID> <COMMANDSTRING/enter*/leave*/center*> 
 
    To add a command you need at least;
      - an areaname, 
      - an unique id for the command 
      - make sure the command works! (when you are op).
	
  #### add a command to an area:

	/fe command <AREANAME> add <COMMANDID> <COMMANDSTRING>

 	  'add' is the default for attaching a command on the 'enter' event. 
	  Using 'enter', 'center' or 'leave' instead of 'add' attaches the new command to 
	  the given eventtype: i.e. /fe command <areaname> center <commandid> <commandstring>

  #### list area commands:
	
	/fe command <AREANAME> list
		
  #### edit a command:
	
	/fe command <AREANAME> edit <COMMANDID> <COMMANDSTRING>
	
  #### change command event:
	
	/fe command <AREANAME> event <COMMANDID> <enter/center/leave>
		
  #### remove command from area:
	
	/fe command <AREANAME> del <COMMANDID>

---

## Development

The Festival plugin is in active development.

 ##### Development on [github.com/genboy/Festival](https://github.com/genboy/Festival)

### Log
  - 2 3 2018
    - update readme.md
    - replace name 'barrier' flag with 'passage' (better name for the flag)
    - Moving license content to own file

  - 27 2 2018
    - No hurt, no damage after falling from /fe tp
    - Area tp to center top & fall into area without damage!

  - 26 2 2018
    - Area messages now popup 
    - Separate readme for poggit release template
    - code clean up to much player instance references

  - 22 2 2018
    - Pre-release 
      - version 1.0.2-11
    - Fix multiplayer in Area
    - Fix to many messages
    - added new cover graphic
    - added transparent infographic v1.0.2-11

  - 21 2 2018
    - Pre-release 
      - version 1.0.1-11
    - info updates
      - readme restyled
      - restyled /fe list and /fe here 
      - added per world area list: /fe list <worldname> 
      - added infographic v1.0.1-11
    - new!: barrier flag 
      - area act as barrier when flag is true
      - whitelisted and ops allowed
    - new!: short commands for flags
      - f for flag: /fe f <AREANAME> <flag> <true/false>
      - fast toggle: /fe <touch/edit/god/msg/barrier> <AREANAME>
    - and some small tweaks

  - 13 2 2018
    - Pre-release 
      - version 1.0.0-11
      - submitted plugin to Poggit
    - Festival area setup and protection based on iprotector
      - area enter, leave en center event detection/messages
      - area object extended with commandlist and eventlist
    - Commands to add custom events
      - add multiple commandstrings with id attachted to enter, leave or center area event
      - list area extended info and manage event commands

#### Milestones 

  - [x] area protection and flag management is stable 
  (core [iProtector](https://github.com/poggit-orphanage/iProtector), [9876ca3](https://github.com/poggit-orphanage/iProtector/commit/9876ca3acd48830599b3715346a1cf8ac964bdbd) Dec 2017) 

  - [x] Area messages and msg/description management are stable
  - [x] Commands can be attachted to specific events at the area: 
  - [x] enter: on entering the area
  - [x] center: when in the center of the area
  - [x] leave: when leaving the area 
  - [x] Submit to poggit
  - [x] Testing expected possibilities; use as portals and shields, design a minigame park, create a quest/parcour.. 
  - [x] Barrier flag; turning the area into a barrier, no one in, no one out.
  
  Many ideas and necessities popped-up and more will be added to [the Bucketlist](https://github.com/genboy/Festival/issues/11) 
  

 
 If you like to help improve this plugin;

- download/use the plugin and give your feedback
- look at the code and give feedback
- both by submitting [issues @ github](https://github.com/genboy/Festival/issues) and/or [reviews @ poggit](https://poggit.pmmp.io/p/Festival) 

or send an email to msg @ genboy.net
	
Thank you


------


## Credits

The area code derives from the [iProctector plugin](https://github.com/LDX-MCPE/iProtector). All credits for the area creation and protection code go to the iProtector creator [LDX-MCPE](https://github.com/LDX-MCPE) and [other iProtector devs](https://github.com/LDX-MCPE/iProtector/network).

In a first fork from [poggit-orphanage](https://github.com/poggit-orphanage/iProtector) the new code was extending the area with enter and leave messages and adding options to attach separate event-objects to an area and trigger specific events with commands. These test versions kept the core iProtector areas unchanged (to be able to use excisting area's). 

These first adjustments worked well being a test plugin but keeping iProtector area's while adding separate event data made me create a split command structure (wich isn't logical or handy) and separate event objects are only needed if the original area class should stay the same. So, for a better plugin command structure and performance the iProtector Area code was used to create the setup for what now has become the Festival Plugin.

