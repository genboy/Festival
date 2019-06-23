

# Festival v2.0.0-dev)
Create a festival with this custom area events plugin for Pocketmine Server:
#####  Manage area's and run commmands attachted to area events. 
(latest stable version [@ poggit https://poggit.pmmp.io/p/Festival](https://poggit.pmmp.io/p/Festival)

![Festival plugin logo large](https://genboy.net/wp-content/uploads/2018/02/festival_plugin_logo.png)
### Early bird Development version for testing only! 
Please report bugs -thank you!

**Download development version**: 
.phar zippackage [phar zipped https://genboy.net/wp-content/uploads/2019/06/Festival_v2.0.0-dev.zip](https://genboy.net/wp-content/uploads/2019/06/Festival_v2.0.0-dev.zip)

or use [devtools plugin](https://poggit.pmmp.io/p/DevTools/1.13.0) and [download zip package https://github.com/genboy/Festival/archive/master.zip](https://github.com/genboy/Festival/archive/master.zip)

**Install**: *(always save copies of your previous used config.yml and areas.json before re-install)*
1. place phar in plugins folder and restart, 
2. after restart;
 2a. if need previous used configs and areas: delete config.json and areas.json from the root folder 
and put your config.yml and areas.json in Festival (root) folder
 2b. if clean start (no areas)  edit /resources/config.yml to your likes and delete config.json from the root folder
3. Then restart again, now areas.json, levels.json and config.json in Festival (root) folder are used.



**Management UI in game**: 
**command** /fe ui(form, config, data)
or **hold magic item** ( default item 201 - Purpur Pillar block - change in config management) 

or use the commands (now with Multi wORLd Full Capitalized names possible)

If you like to use Festival consider [sharing your experience and issues](https://github.com/genboy/Festival/issues) to fix any usability problems before posting a [vote](https://poggit.pmmp.io/p/Festival/1.1.1)! That way it will improve Festival, my coding skills, your Pocketmine-MP insights and strenghten the PMMP community, thank you!
 
!Take notice of the Copyright Statement if you use Festival for the first time since 27 April 2019. 
**Read the Legal Notice** at the bottom of this README file or the Legal Notice tab at poggit.pmmp.io/p/Festival

###### Copyright [Genboy](https://genboy.net) 2018 
 
--- 

# Festival

[![](https://poggit.pmmp.io/shield.state/Festival)](https://poggit.pmmp.io/p/Festival) 
[![](https://poggit.pmmp.io/shield.api/Festival)](https://poggit.pmmp.io/p/Festival)
[![](https://poggit.pmmp.io/shield.dl.total/Festival)](https://poggit.pmmp.io/p/Festival)
[![](https://poggit.pmmp.io/shield.dl/Festival)](https://poggit.pmmp.io/p/Festival)


#### Features

### UI forms for area, level & config management!

> magic item to open UI by default id 201 - Purpur Pillar block - see config.json

### + Sphere type area's by radius or diameter :)
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

    - Define area's by tapping 2 positions
      - diagonal for cube
      - radius for sphere
      - diameter for sphere
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


**Specific**



## Usage 
  
  - ! Update Festival 2 in development using ingame UI for configurations

  - Standard Plugin installation; Upload .phar file to server 'plugin' folder (or upload .zip if you have latest devtools installed), restart the server, go to  folder plugins/Festival;

  - read [wiki on configurations](https://github.com/genboy/Festival/wiki/2.-Install,-Configure-&-Update)

  - Edit config.yml; set the defaults for options, default area flags and the default area flags for specific worlds.

 
  ### Updates
  
  Updates available at [poggit](https://poggit.pmmp.io/ci/genboy/Festival/Festival) and [github](https://github.com/genboy/Festival/releases)
  
  ##### !Before update always copy your config.yml and areas.json files to a save place, with this you can revert your Festival installation
  - after .phar install and first restart/reload plugins; check console info and your areas.json and config.yml; restart after adjusted correctly 
  
  - ! Update Festival 2 in development translating resource config.yml or your mainfolder config.yml and areas.json on install

  #### Usage Graphic

  ##### A visualisation of Festival command usage
  
  To be updated soon..
  
  ###### Copyright [Genboy](https://genboy.net) 2018
  

 
  #### Language
  Command: /fe lang <en/nl>
  Set Festival language en/nl/es/pl for area and command returned messages. 
  en = english
  nl = nederlands 
  
  todo: es = Espanol, pl = Polski
  
  __ = your language, please help [translate __.js](https://github.com/genboy/Festival/blob/master/resources/en.json)
 

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
 ###### Copyright [Genboy](https://genboy.net) 2018 - markdown edited with [stackedit.io](https://stackedit.io) 
