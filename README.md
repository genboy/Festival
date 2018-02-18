# Festival
Custom Area Events plugin for Pocketmine Server ALPHA10+

The Festival plugin: manage and run commmands on events attachted to a specified area.

#### Festival time!
- create and manage area's (like iProtector)
- add an area description, flag it to hide for non-ops
- set the flag to false to
  - edit: allow building/breaking
  - god: give players god mode 
  - touch: allow interaction with chests etc.  
  - msg: show enter/leave area messages to non-ops 
  - barrier: prevent players to enter/leave the area
- manage area events
  - add/edit/delete commands (with their own id) for each area
  - assign commands to area eventtypes enter, center and leave.

## Usage

#### Basic area features (like iProtector - /area = /fe)

	- first select position 1, then select position2, 
	  the endpoints of the longest diagonal in the area

		/fe pos1
		/fe pos2

	- name/save the selected area

		/fe create <AREANAME>  

	- set the area flags
	
		/fe flag <AREANAME> <god/build/touch/msg/barrier> <true/false>

	- see info on the area's you're in
	
		/fe here

	- see a list of all area's
	
		/fe list

	- teleport to an area
	
		/fe tp <AREANAME>
  
	- set area description
		
		/fe desc <AREANAME> <DESCRIPTION>
  
	- manage players in area whitelist
	
		/fe whitelist <AREANAME> <add/list/remove> <PLAYERNAME>
  
	- delete an area
	
		/fe delete <AREANAME>
  


#### Extended area features
command to attach commands to an area

/fe command AREANAME add(1)/list/edit/event(2)/del COMMANDID COMMANDSTRING/enter/leave/center 

To add an command you need at least an areaname, an unique id for the command and make sure the command works! (when you're an op).

Usage:
	
	add a command to an area:

		/fe command <AREANAME> add <COMMANDID> <COMMANDSTRING>

		'add' is the default for attaching a command on the 'enter' event. 
		Using 'enter', 'center' or 'leave' instead of 'add' attaches a new command to 
		the eventtype: i.e. /fe command <areaname> center <commandid> <commandstring>

	list area commands:
	
		/fe command <AREANAME> list
		
	edit a command:
	
		/fe command <AREANAME> edit <COMMANDID> <COMMANDSTRING>
	
	change command event:
	
		/fe command <AREANAME> event <COMMANDID> <enter/center/leave>
		
	remove command from area:
	
		/fe command <AREANAME> del <COMMANDID>



## In progress

The plugin is in active development; 
  - [x] area protection and flag management is stable 
  (core code from [iProtector](https://github.com/poggit-orphanage/iProtector) 
  Latest commit [9876ca3](https://github.com/poggit-orphanage/iProtector/commit/9876ca3acd48830599b3715346a1cf8ac964bdbd) on 11 Dec 2017) 

  - [x] Area messages and msg/description management are stable

  - [x] Commands can be attachted to specific events at the area: 
  - [x] enter: on entering the area
  - [x] center: when in the center of the area
  - [x] leave: when leaving the area

  - [x] Submit to poggit
  
	=> Testing possibilities; use as portals and shields, design a minigame park, create a quest/parcour.. 

  - [ ] Develop a method to sync/log other plugins/Multiplayer/Timeline/Story/Minigame attachted events in given area's
  - [ ] options to add more area event types
  - [ ] add an area tranformer method - using or copying iProtector area's for Festival events 
  - [ ] Add an UI panel  

### History

The area code derives from the [iProctector plugin](https://github.com/LDX-MCPE/iProtector), in a first fork from [poggit-orphanage](https://github.com/poggit-orphanage/iProtector) the new code was extending the area with enter and leave messages and lateron also adding options to attach separate event-objects to an area and trigger specific events with commands. These test versions kept the core iProtector areas unchanged (to be able to use excisting area's).
These first adjustments worked well being a test plugin but keeping iProtector area's while adding separate event data made me create a split command structure (wich isn't logical or handy) and separate event objects are only needed if the original area class should stay the same. So, for a better plugin command structure and performance the iProtector Area code was used to create the setup for what now has become the Festival Plugin.
