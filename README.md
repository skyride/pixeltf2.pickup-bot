The year is 2008 and I'm a high school student. For a couple years I've been playing around
with Linux, making basic, visually terrible PHP websites with table-based layouts and glaring
security holes, and recently got enough money to rent myself a 256MB RAM VPS from Linode.

I've also been playing this hot new game called Team Fortress 2 which just had it's first
big update giving the Medic 3 alternative weapons, eventually we hope Valve will get around
to giving every class alternative weapons, maybe they'll even add cosmetics while they're at it!

The competitive community in Europe existed mostly as an amalgamation of posters on etf2l.org
and several channels on the QuakeNet IRC server. There's a few match making systems based on IRC
bots but they all suck for a variety of mostly social/gameplay reasons. I decide to make my own
"pickup" channel as they were known.


# Pixel Pickup Bot

Pixel Pickup was an IRC channel I ran from early 2009 to mid 2011 using the bot in this repository.
This codebase is the bot as it finally rested the last time I touched it probably late 2010. Github
had just passed its first birthday where it was revealed to have a whopping 40,000 repositories
and later that year a whopping 100,000 active users. Naturally then, I lacked any version control
though I was vaguely aware of SVN.

There's a couple log files I've left in since they contain no sensitive information. The config
files also have sensitive information blanked out as I recently had the cunning idea to
put my passwords and database info in a seperate `config.php` so that I could avoid having to
change it in multiple places.

## Architecture

* PHP 5.2
* MySQL 5(.1?)
* [Net_SmartIRC](https://github.com/pear/Net_SmartIRC)

I'm actually quite thankful to my 16 year old self for so thoroughly commenting the codebase.
Although the structure of the project is a mess there is actually a lot explaining how things work.

Since PHP is single threaded and I didn't know how to handle concurrency, the main bot runs
in a single thread which handles primary I/O and commands. Any action that needed to be done
en-masse would either run in a forked thread under a different IRC user that also had channel
Operator, or was triggered via a `system()` call as a completely seperate sub process.

If the bot needed a delay (e.g. the 30 second wait period where everyone is asked to indicate
they are !ready), it would start one of these subprocesses with a randomly generated key as
an argument, sleep 30 seconds, then join QuakeNet under a seperate user account and PM itself
this key, this triggering the event that would continue the matchmaking flow.

The channel used several providers, ultimately being donated 4 servers from [Multiplay](https://multiplay.co.uk/)
on which matches were scheduled. When a match was started the bot would fire off RCON commands
to load the correct map, set up an STV relay so other other users not playing could watch,
and provide Mumble server connection information so all players could communicate.

The ability to pick the class you wished to play and using Mumble over Ventrillo were the key
factors that made it become the go-to place for casual competitive play within the European TF2 community.

## Reflections

Although the codebase its a complete mess it means a to me. It was the first thing I ever built
that garnered an actual user base - around 200-300 weekly active users for a couple years. Far
more than engineering lesson in the software itself, the beginnings of virtually all the soft
skills I use in my career today were born here. Taking non-technical user feedback, dealing with
folks angry about service interruption, working with third parties who donated hosting on
ocassions, and moderating an at times extremely hostile community.

On the off chance that anyone who used this channel even once is reading this, I'd like to thank
you for doing so. As well as teaching me so much, creating, growing and nurturing the pixel
pickup community was a shining beacon of pride during a period of my life where I didn't
have much else going for me.