# Festival
Custom Area Events plugin for Pocketmine Server ALPHA10+

The Festival plugin: manage and run commmands on specific events attachted to a specific area.

#### Festival time!
- create and manage area's like with iProtector
- add an area description
- set the flag to false to
  - edit: allow building/breaking
  - god: give players god mode 
  - touch: allow interaction with chests etc.  
  - msg: show enter/leave area messages to non-ops 
- manage area events
  - add/edit/delete commands (with their own id) for each area
  - assign the commands to the eventtypes enter, center and leave.

## Usage

#### Basic area features (like iProtector - /area = /fe)

/fe pos1/pos2

/fe create AREANAME  
  
/fe flag AREANAME god/build/touch/msg
  
/fe here

/fe list

/fe tp AREANAME
  
/fe desc AREANAME DESCRIPTION
  
/fe whitelist AREANAME PLAYERNAME
  
/fe delete AREANAME
  


#### Extended area features
command to attach commands to an area

/fe command AREANAME add(1)/list/edit/event(2)/del COMMANDID COMMANDSTRING/enter/leave/center 

To add an command you need at least an areaname, an unique id for the command and make sure the command works! (when you're an op).

Usage:
	
	add a command on area enter:

		/fe command AREANAME <add> COMMANDID COMMANDSTRING

		'add' is the default for attaching a command on the 'enter' event. 
		Using 'enter', 'center' or 'leave' instead of 'add' attaches a new command to the given eventtype: 
		i.e. /fe command <areaname> center <commandid> <commandstring>

	edit a command:
	
		/fe command AREANAME <edit> COMMANDID COMMANDSTRING
	
	change command event:
	
		/fe command AREANAME <edit> COMMANDID <enter/center/leave>
		
	remove command from area:
	
		/fe command AREANAME <del> COMMANDID


Sum up:

/fe <pos1/pos2/create/list/here/tp/desc/flag/delete/whitelist/command> <areaname> <add/enter/leave/center/list/event/edit/del> <cmdid> <cmdstr/enter/leave/center>



## In progress

The plugin is in active development; 
1. [x] area protection and flag management is stable 
(core code from [iProtector](https://github.com/poggit-orphanage/iProtector) 
Latest commit [9876ca3](https://github.com/poggit-orphanage/iProtector/commit/9876ca3acd48830599b3715346a1cf8ac964bdbd) on 11 Dec 2017) 

2. [x] Area messages and msg/description management are stable

3. [x] Commands can be attachted to specific events at the area: 
  - [x] enter: on entering the area
  - [x] center: when in the center of the area
  - [x] leave: when leaving the area
  - [ ] ?: more area event types to consider

4a. [ ] Submit to poggit

4b. [ ] Gather code and concept suggestions 

=> Testing possibilities;
    - design a minigame park
    - create a quest/parcour
    - use stable wormholes
    - .. Endless right? This step will take a while :)

5. [ ] we can try to add an area tranformer method - using or copying iProtector area's for Festival events 

6. [ ] Develop a method to sync/log other plugins/Multiplayer/Timeline/Story/Minigame attachted events in given area's




### History

The area code derives from the [iProctector plugin](https://github.com/LDX-MCPE/iProtector), in a first fork from [poggit-orphanage](https://github.com/poggit-orphanage/iProtector) the new code was extending the area with enter and leave messages and lateron also adding options to attach separate event-objects to an area and trigger specific events with commands. These test versions kept the core iProtector areas unchanged (to be able to use excisting area's).
These first adjustments worked well being a test plugin but keeping iProtector area's while adding separate event data made me create a split command structure (wich isn't logical or handy) and separate event objects are only needed if the original area class should stay the same. So, for a better plugin command structure and performance the iProtector Area code was used to create the setup for what now has become the Festival Plugin.
