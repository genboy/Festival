## Overview

![Festival plugin logo large](https://genboy.net/wp-content/uploads/2018/02/festival_plugin_logo.png)


Create a festival with this custom area events plugin for Pocketmine Server ALPHA10+:

### Manage area's and run commmands attachted to area events.


![Festival command usage overview](https://genboy.net/wp-content/uploads/2018/02/festival_usage_1.0.2-11.png)

###### Copyright [Genboy](https://genboy.net) 2018


## Info

# Festival

[![](https://poggit.pmmp.io/shield.state/Festival)](https://poggit.pmmp.io/p/Festival)
 [![](https://poggit.pmmp.io/shield.api/Festival)](https://poggit.pmmp.io/p/Festival)


[issues @ github](https://github.com/genboy/Festival/issues) and/or [reviews @ poggit](https://poggit.pmmp.io/p/Festival)

#### Features

- create and manage area's (like WorldGuard/iProtector)
  - define cuboid area with 2 positions and name/create the area 
  - add an area description
  - whitelist players for the area
  - set the area flags to true to
    - edit: disallow building/breaking
    - god: deny players god mode
    - touch: disallow interaction with chests etc.
    - msg: show enter/leave area messages to non-ops
    - barrier: only ops and whitelisted players can enter or leave the area! (new! v1.0.1-11)
- manage area events
  - assign commands to area events
  - event types: enter, center and leave.
  - add/edit/list/delete area event commands 
  - change eventtype of commands


##### ! Important note: Each area event holds an array of commands to be triggered with OP permission from the player.


###### Created by [Genboy](https://genboy.net) 2018

Credits for most area protection code go to iProtector creator [LDX-MCPE](https://github.com/LDX-MCPE) and all [other iProtector devs](https://github.com/LDX-MCPE/iProtector/network).

  
-------


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
	
	/fe flag(f) <AREANAME> <god/build/touch/msg/barrier> <true/false>
		
  #### fast flag toggle (new! v1.0.1-11)
		
 	/fe <god/build/touch/msg/barrier> <AREANAME> (<true/false>)
		
  
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


------

## Development

The Festival plugin is in active development.

 ##### Development on [github.com/genboy/Festival](https://github.com/genboy/Festival)

### Log

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



## License

                   GNU LESSER GENERAL PUBLIC LICENSE
                       Version 3, 29 June 2007

 Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 Everyone is permitted to copy and distribute verbatim copies
 of this license document, but changing it is not allowed.


  This version of the GNU Lesser General Public License incorporates
the terms and conditions of version 3 of the GNU General Public
License, supplemented by the additional permissions listed below.

  0. Additional Definitions.

  As used herein, "this License" refers to version 3 of the GNU Lesser
General Public License, and the "GNU GPL" refers to version 3 of the GNU
General Public License.

  "The Library" refers to a covered work governed by this License,
other than an Application or a Combined Work as defined below.

  An "Application" is any work that makes use of an interface provided
by the Library, but which is not otherwise based on the Library.
Defining a subclass of a class defined by the Library is deemed a mode
of using an interface provided by the Library.

  A "Combined Work" is a work produced by combining or linking an
Application with the Library.  The particular version of the Library
with which the Combined Work was made is also called the "Linked
Version".

  The "Minimal Corresponding Source" for a Combined Work means the
Corresponding Source for the Combined Work, excluding any source code
for portions of the Combined Work that, considered in isolation, are
based on the Application, and not on the Linked Version.

  The "Corresponding Application Code" for a Combined Work means the
object code and/or source code for the Application, including any data
and utility programs needed for reproducing the Combined Work from the
Application, but excluding the System Libraries of the Combined Work.

  1. Exception to Section 3 of the GNU GPL.

  You may convey a covered work under sections 3 and 4 of this License
without being bound by section 3 of the GNU GPL.

  2. Conveying Modified Versions.

  If you modify a copy of the Library, and, in your modifications, a
facility refers to a function or data to be supplied by an Application
that uses the facility (other than as an argument passed when the
facility is invoked), then you may convey a copy of the modified
version:

   a) under this License, provided that you make a good faith effort to
   ensure that, in the event an Application does not supply the
   function or data, the facility still operates, and performs
   whatever part of its purpose remains meaningful, or

   b) under the GNU GPL, with none of the additional permissions of
   this License applicable to that copy.

  3. Object Code Incorporating Material from Library Header Files.

  The object code form of an Application may incorporate material from
a header file that is part of the Library.  You may convey such object
code under terms of your choice, provided that, if the incorporated
material is not limited to numerical parameters, data structure
layouts and accessors, or small macros, inline functions and templates
(ten or fewer lines in length), you do both of the following:

   a) Give prominent notice with each copy of the object code that the
   Library is used in it and that the Library and its use are
   covered by this License.

   b) Accompany the object code with a copy of the GNU GPL and this license
   document.

  4. Combined Works.

  You may convey a Combined Work under terms of your choice that,
taken together, effectively do not restrict modification of the
portions of the Library contained in the Combined Work and reverse
engineering for debugging such modifications, if you also do each of
the following:

   a) Give prominent notice with each copy of the Combined Work that
   the Library is used in it and that the Library and its use are
   covered by this License.

   b) Accompany the Combined Work with a copy of the GNU GPL and this license
   document.

   c) For a Combined Work that displays copyright notices during
   execution, include the copyright notice for the Library among
   these notices, as well as a reference directing the user to the
   copies of the GNU GPL and this license document.

   d) Do one of the following:

       0) Convey the Minimal Corresponding Source under the terms of this
       License, and the Corresponding Application Code in a form
       suitable for, and under terms that permit, the user to
       recombine or relink the Application with a modified version of
       the Linked Version to produce a modified Combined Work, in the
       manner specified by section 6 of the GNU GPL for conveying
       Corresponding Source.

       1) Use a suitable shared library mechanism for linking with the
       Library.  A suitable mechanism is one that (a) uses at run time
       a copy of the Library already present on the user's computer
       system, and (b) will operate properly with a modified version
       of the Library that is interface-compatible with the Linked
       Version.

   e) Provide Installation Information, but only if you would otherwise
   be required to provide such information under section 6 of the
   GNU GPL, and only to the extent that such information is
   necessary to install and execute a modified version of the
   Combined Work produced by recombining or relinking the
   Application with a modified version of the Linked Version. (If
   you use option 4d0, the Installation Information must accompany
   the Minimal Corresponding Source and Corresponding Application
   Code. If you use option 4d1, you must provide the Installation
   Information in the manner specified by section 6 of the GNU GPL
   for conveying Corresponding Source.)

  5. Combined Libraries.

  You may place library facilities that are a work based on the
Library side by side in a single library together with other library
facilities that are not Applications and are not covered by this
License, and convey such a combined library under terms of your
choice, if you do both of the following:

   a) Accompany the combined library with a copy of the same work based
   on the Library, uncombined with any other library facilities,
   conveyed under the terms of this License.

   b) Give prominent notice with the combined library that part of it
   is a work based on the Library, and explaining where to find the
   accompanying uncombined form of the same work.

  6. Revised Versions of the GNU Lesser General Public License.

  The Free Software Foundation may publish revised and/or new versions
of the GNU Lesser General Public License from time to time. Such new
versions will be similar in spirit to the present version, but may
differ in detail to address new problems or concerns.

  Each version is given a distinguishing version number. If the
Library as you received it specifies that a certain numbered version
of the GNU Lesser General Public License "or any later version"
applies to it, you have the option of following the terms and
conditions either of that published version or of any later version
published by the Free Software Foundation. If the Library as you
received it does not specify a version number of the GNU Lesser
General Public License, you may choose any version of the GNU Lesser
General Public License ever published by the Free Software Foundation.

  If the Library as you received it specifies that a proxy can decide
whether future versions of the GNU Lesser General Public License shall
apply, that proxy's public statement of acceptance of any version is
permanent authorization for you to choose that version for the
Library.