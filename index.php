<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* main file, includes all the calculations and most database access */

/* this file can be reached:

1.  from index.html, when the user has just logged in or has read or sent a message ($action = login)
      ($loginname contains user's name, $boat and $email may contain user's boat name and email address)
2.  from itself, when the user has selected an investment ($action = gofishing)
3.  from itself, when the user has logged out ($action = logout)
4.  from itself, when the user has just sent or read a message ($action = message)

*/


$debug = 0;  /*set to true for debugging output */

include("common.inc");

/* maintain the player's name and boat name between pages */

session_start();
if (isset($_SESSION['name'])) $name = $_SESSION['name']; else $name = "";
if (isset($_SESSION['boat'])) $boat = $_SESSION['boat']; else $boat = "";
if (isset($_REQUEST['action'])) $action = $_REQUEST['action']; else $action = "";
if (isset($_REQUEST['investment'])) $investment = $_REQUEST['investment'];
else $investment = 0;

debug("name", $name);
debug("boat", $boat);
debug("action", $action);

$clockTime = time();  // the time on the simulated clock on the wall

/* declare classes for program objects */

        /* Formulae:
        
        quantity fished = investment * stock * k1 * random number
        left in sea = stock - quantity fished
        market price = k2 - k3 * (sum of fish caught in the last 4 expeditions)
        fisher's income = market price * quantity fished 
        new balance = old balance + fisher's income - maintenance - investment
        new stock = left in sea + k5 * (k6 - left in sea) / left in sea
        
        where  (these are all set in defines.inc)
            k1 relates investment to quantity fished
            k2 is maximum market price (price when no fish are in market)
            k3 determines slope of demand curve
            k4 is minimum price of fish, even when there is an oversupply
            k5 is growth rate of fish when sea is half stocked
            k6 is maximum capacity of sea
        */
  

class Sea {
	public $stock, $marketPrice, $lastupdate, $maintenance;

	function retrieve () {
		$query = new query("SELECT stock, price, date_part('epoch', time) as lastupdate, maintenance
                            FROM sea ORDER BY time DESC LIMIT 1");
		if (!$query->next_rec()) $query->error("No records retrieved"); 
		$this->stock = $query->field("stock");
		$this->marketPrice = $query->field("price");
		$this->lastUpdate = $query->field("lastupdate");
		$this->maintenance = $query->field('maintenance');
		if ($this->stock < 0) include("deadsea.inc");
		}

	function save() {
		/* $clockTime is number of seconds since the epoch */
                
		global $clockTime;
                
		$timestamp = date('r', $clockTime);  debug("timestamp", $timestamp);
		db_write("INSERT INTO sea (stock, price, time, maintenance) 
                        VALUES('$this->stock', '$this->marketPrice', '$timestamp', '$this->maintenance')");
		}

	function growFish() {
        
		global $k5, $k6;
                
		$this->stock = round($this->stock + ($k5 * ($k6 - $this->stock) * $this->stock)/100000);
		if ($this->stock < 0) $this->stock = 0;
		}
	
	function sellFish($quantityFished) {

		global $log, $k2, $k3, $k4;

		$this->stock -= $quantityFished; 
		if ($this->stock < 0) include("deadsea.inc");
		                                                            debug("leftInSea", $this->stock);
		$log->retrieve();
		$log->fishInMarket += $quantityFished;
		$this->marketPrice = $k2 - $k3 * rand(1, 100) * $log->fishInMarket; 
		if ($this->marketPrice < $k4) $this->marketPrice = rand(1,100)/100.0 + $k4; 
		                                                   debug("market price", $this->marketPrice);
		$this->save();
		return $this->marketPrice * $quantityFished - $this->maintenance; 
        }     
}

class Fisher {
	public $name, $boat, $type, $logintime, $balance, $email;

	function retrieve($myName) {
		$this->name = $myName;
		$query = new query("SELECT boat, balance, logintime, email FROM people 
			WHERE name = '$myName'");
    if (!$query->next_rec()) $query->error("No records retrieved"); 
		$this->boat = $query->field('boat');
		$this->balance = $query->field("balance"); 		
    $this->logintime = $query->field('logintime');
    $this->email = $query->field('email');
		}

	function save() {
    global $clockTime;
                
    $timestamp = date('r', $clockTime);
		db_write("UPDATE people SET balance='$this->balance', lastoptime= '$timestamp' 
				WHERE name='$this->name'");
		}

	function catchFish($investment) {

		global $sea, $log;
		global $k1;

    if ($investment < 0) $investment = 0;
		if ($investment > $this->balance) $investment = $this->balance;  /* no borrowing! */ 
                                                            debug("initial balance", $this->balance);
    $quantityFished = round($investment * $sea->stock * rand(1, 10) * $k1);  
                                                            debug("quantityFished", $quantityFished);
		$income = $sea->sellFish($quantityFished);
                                                            debug("income", $income);
    $this->balance = round($this->balance + $income  - $investment); 
                                                            debug("balance after income", $this->balance);
		$log->save($this->name, $investment, $quantityFished, $this->balance);
		$this->save();
		}
}

class Phantom extends Fisher {
  public $code;
        
  function invest() {
    global $sea, $k6;
            
		$query = new query("SELECT code FROM phantoms WHERE name='$this->name'");
		if (!$query->next_rec()) $query->error("No records retrieved"); 
		$this->code = $query->field('code');
		$investment = eval($this->code);
		$this->catchFish($investment);
		if ($this->balance < 0) $this->bankrupt();
		}
            
function bankrupt() {
		mail($this->email, "Gone Fishing! Your agent has gone bankrupt",
		                   "Unfortunately, your fisher agent has run out of money and has been retired from 
		                    fishing.
		                            The Fishing Authority");
		}
}

class Log {
	public $fishInMarket, $averageCatchSize, $averageInvestment;

	function retrieve() {
		$this->fishInMarket = 0;
		$totalRecentInvestment = 0 ;
		$catches = 0;
		$query = new query("SELECT investment, harvest FROM log WHERE harvest > 0 ORDER BY id DESC LIMIT 4");
		while ($query->next_rec()) {
			$this->fishInMarket +=  $query->field("harvest");  		debug("in market", $this->fishInMarket);
			$totalRecentInvestment += $query->field("investment");
			$catches++;
			}
		if ($catches) {
			$this->averageCatchSize = round($this->fishInMarket/$catches);
			$this->averageInvestment = round($totalRecentInvestment/$catches);
			}
		}

	function save($name, $investment, $quantityFished, $balance) {
		global $clockTime;
		            
		$timestamp = date('r', $clockTime);
		db_write("INSERT INTO log (name, investment, harvest, balance, time) 
			                  VALUES ('$name', '$investment', '$quantityFished', '$balance', '$timestamp')");
		}		
}
		
	


/* create instances for the human player, the sea and the log of operations */

$sea = new Sea();
$fisher = new Fisher();
$log = new Log();

/* open the database */

$db = db_open();

/* update the environment state with whatever actions should have happened during the period since a player
   was last here (this is necessary because the program never does anything except when a player tries to
   retrieve a web page; so actions which should have taken place while nobody was doing anything are
   simulated here as though they happened when they were supposed to).  This is the place to run agents and
   so on. */
   
$sea->retrieve();

updateWorld();

/* check if player just logged in.  If so, register him or her in the database*/

if ($action == 'login') {
	register();
	}
else {
	$welcome_back = "fishing";
}

/* retrieve previous state before processing action */

$fisher->retrieve($name);
$boat = $fisher->boat;

/* despatch to selected action */

switch ($action) {
	case 'login':
	case 'message': goFishing();
                    break;

	case 'logout':	goodbye();
                    break;

	case 'gofishing':
	default:		if ($investment > 0 )
										$fisher->catchFish($investment);
                    goFishing();
                    break;
	}
    
/* logout any inactive players */

checkPlayers();

html_footer();
    

function updateWorld() {

    /* update the environment and run any computational agents    
    Called every time a human user does anything */
    
    global $sea;    
    global $clockTime, $updateInterval, $phantomActivityRate;
      
        
    /* first, retrieve any phantoms that will be operational duringthe updating */
    
    $query = new query("SELECT name FROM people WHERE type = 'agent'");
    while ($query->next_rec()) {
            $phantom = new Phantom();
            $phantom->retrieve($query->field('name'));
            if ($phantom->balance > 0) $phantoms[] = $phantom;
            }

    /* for each interval between the last operation and now, update the world.  
    Always update at least once, but don't update over more than 24 hours 
    (86400 secs) */

    $clockTime = $sea->lastUpdate;
    $now = time();
	if ($now - $clockTime > 84600) $clockTime = $now - 84600;
    do {
        $clockTime += $updateInterval;
        if ($clockTime > $now) $clockTime = $now;

		$sea->growFish();
		$sea->save();
        
        /* give the phantom agents a go, every few turns */
        
        if ($phantoms) {
            foreach ($phantoms as $phantom) { 
                if (rand(0, 100) <= $phantomActivityRate) {  /*only activate the phantoms now and then */
                    $phantom->invest();
                }
            }
        }
   
        include("offish.inc");
        
        } while ($clockTime < $now);
}

function checkPlayers() {

/* log out any player who has not done anything for more than $idleTime seconds */

    global $idleTime;
    
    $query = new query("SELECT name, date_part('epoch', lastoptime) as lasttime FROM people 
						WHERE logintime IS NOT NULL AND type != 'agent'");
    while ($query->next_rec()) {
        if (time() - $query->field('lasttime') > $idleTime) {
            $idler = $query->field('name');
            db_write("UPDATE people SET logintime = NULL WHERE name= '$idler'");
            }
        } 
}

function register () {

    /* here if user has just logged in;
    register name in database if this is a new user
    or retrieve name and boat name if returning */

    global $name, $boat, $welcome_back;

    $name = $_POST['loginname'];
    $loginboat = $_POST['loginboat'];
    $email = $_POST['email'];   
    if (!$name) {  
        alert("You must provide your name.");
        exit;
        }

    $query = new query("SELECT * FROM people WHERE name ~* '$name'");
    if ($query->num_recs()) {
        /* user is already known */
        $query->next_rec();
        $name = $query->field('name'); /* standardise name to what is in the DB */
        $boat = $query->field('boat');
        /* record time when logged in */  debug("name in register", $name);
        db_write("UPDATE people SET logintime=now(), lastoptime=now() WHERE name='$name'");
        $welcome_back = "old_user";
        }
    else {  
        /* new user */
        if (!$loginboat) {
            alert("You must name your fishing boat.");
            exit;
            }
        $query = new query("SELECT boat FROM people WHERE boat='$loginboat'");
        if ($query->num_recs()) {
            alert("Sorry. That name is already taken for a boat.  Please choose another.");
            exit;
            }
        $boat = $loginboat;
        db_write("INSERT INTO people (name, boat, createdtime, logintime, lastoptime, balance, email) 
                                        VALUES ('$name', '$boat', now(), now(), now(), 10000, '$email')");           
        $welcome_back = "new_user";
        }
    $_SESSION['name'] = $name;
    $_SESSION['boat'] = $boat;
}

            
function goodbye() {

    /* here if user has just logged out 
    note that they are no longer at sea */

	global $name;

    db_write("UPDATE people SET logintime=null WHERE name='$name'");
    include("goodbye.inc");
    exit;	
}

function goFishing () {

    /*  here if the user wants to go fishing */

	global $fisher, $sea, $log, $name, $welcome_back, $agent_activity, $investment;
        
    if ($fisher->balance < 0) {
        include("bankrupt.inc");
        }
    if (!$fisher->logintime) {
        /* user has been logged out for exceeding the idle time */
        include("logout.inc");
        }
    else {
        $log->retrieve();
        include("fish.inc");
        }
}

 
function radioAll($msg) {

    /* send a radio message to all human fishers that are currently logged in */
    
    global $name, $clockTime;

    $timestamp = date('r', $clockTime);
    $query = new query("SELECT boat FROM people 
                            WHERE name != '$name' AND createdtime IS NOT NULL AND logintime IS NOT NULL");
    while ($query->next_rec()) {
        $recipient = $query->field("boat"); 
        db_write("INSERT INTO msgs (sender, recipient, timesent, timeread, msg) 
                    VALUES(NULL, '$recipient', '$timestamp', NULL, '$msg')");
    }
}

/* ENDS */
?>


    