<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* switch page for administration.  Linked to from admin.html

*/

include("common.inc");

html_header("Gone Fishing! administration");

?>

<H1>Gone Fishing! administration</H1>
<hr>
<blockquote>

<?php

$db = db_open();
$choice = $_POST['choice'];
    switch ($choice) {
        case "Reset":
            doReset($db, "Reset");
            break;
        case "Restart":
            doReset($db, "Restart");
            break;
        case "msgall":
            msgAll($db);
            break;
        case "players":
            displayPlayers($db);
            break;
        case "boatlogs": 
            displayBoatLogs($db);
            break;
        case "sealog":
            displayFish($db);
            break;
        case "msglog":
            displayMsgs($db);
            break;
        default:
            die("Problem with button choice: $choice");
        }
?>

<form method=get action="admin.html">
    <input type=submit name=return value="Return to main page">
</form>
</blockquote>

<?php

html_footer(); 

function doReset($db, $type) {
/* clear the log and sea tables, and reset the number of fish in the sea.
Also return everyone to port, or remove them entirely if a restart */

        $query = new query("SELECT * FROM people WHERE logintime IS NOT NULL");
        if ($query->num_recs() > 0) {
            printf("<table border=0 bgcolor=red><tr><th>Warning: The following are still at sea!</td></tr>");
            while ($query->next_rec()) {
			   printf("<tr><td align=center>%s</td></tr>", $query->field("name"));
		       }
            printf("</table>");
            }
?>

<P>Enter password:</P>
<form method=post action="reset.php">
    <input type=password name=password>
    <input type=hidden name=type value='<?php echo $type ?>'>
    <input type=submit name=submit value="<?php echo $type ?> game">
</form>
</font>
<pre>





</pre>

<?php
}

function msgAll($db) {
?>

<P>Enter password:</P>
<form method=post action="msgall.php">
    <input type=password name=password>
    <input type=submit name=submit value="Go">
</form>

<?php
}

function displayPlayers($db) {
/* display data about all the players */

	$sortby = $_POST['sortby'];
    if (!$sortby) $sortby = "balance";

?>

<H2>Players</H2>
      <table border="0" cellspacing="0" cellpadding="3" align="center">
        <tr>
            <td STYLE="font-size: x-small"><a href="admin.php?choice=players&sortby=name"><b>Name</b></a></td>
            <td STYLE="font-size: x-small"><b><a href="admin.php?choice=players&sortby=boat">Boat</b></td>
            <td STYLE="font-size: x-small"><b>Email</b></td>
            <td STYLE="font-size: x-small"><b><a href="admin.php?choice=players&sortby=createdtime">Registered</b></td>
            <td STYLE="font-size: x-small"><b><a href="admin.php?choice=players&sortby=logintime">Logged in</b></td>
            <td STYLE="font-size: x-small"><b><a href="admin.php?choice=players&sortby=lastoptime">Last trip</b></td>
            <td STYLE="font-size: x-small"><b><a href="admin.php?choice=players&sortby=balance">Balance</b></td>
        </tr>
<?php 
       $query = new query("SELECT name, boat, email, 
                                to_char(createdtime, 'DD Mon YYYY, HH24:MI') as registered, 
                                to_char(logintime, 'DD Mon YYYY, HH24:MI') as loggedtime, 
                                to_char(lastoptime, 'DD Mon YYYY, HH24:MI') as lasttrip,
                                balance 
                                FROM people ORDER BY $sortby $direction");
       while ($query->next_rec()) {
           printf("<tr>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   </tr>", $query->field("name"), $query->field("boat"), $query->field("email"), 
                   $query->field("registered"), $query->field("loggedtime"), 
                   $query->field("lasttrip"), $query->field("balance"));
           }
      echo "</table>";

}

function displayFish($db) {
/* display the history of the number of fish in the sea */

    $start = $_POST['start'];
    
	$chunk = 200;  // number of entries to display at a time

    $query = new query("SELECT to_char(time, 'DD Mon YYYY, HH24:MI') as date, stock, price FROM sea ORDER BY time");
    $nrows = $query->num_recs();
    printf("<H2>History of fish stocks (entries %d to %d)</H2>", $start, min($nrows, $start+$chunk));
    echo "Range of entries to display:<BR>";
       echo "<font size=2>";
        for ($i = 0; $i < $nrows; $i+=$chunk) {
            if ($bar) echo " | ";
            $bar = 1;
            $end = min($nrows, $i + $chunk - 1);
            printf("<a href=\"admin.php?choice=sealog&start=%d\">%4d-%4d</a>\n", $i, $i, $end);
            }
?>
      </font><p>
      <table border="1" cellspacing="0" cellpadding="3" align="center">
        <tr>
            <td STYLE="font-size: x-small"><b>Time</b></td>
            <td STYLE="font-size: x-small"><b>Number of fish</b></td>
            <td STYLE="font-size: x-small"><b>Market price</b></td>
        </tr>
<?php
        $query->seek($start);
        
       for($i = 0; $i < $chunk; $i++) {
            if(!($query->next_rec())) break;
            printf("<tr>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                    <td STYLE=\"font-size: x-small\">%s</td>
                  </tr>", $query->field("date"), $query->field("stock"), $query->field("price"));
           }
      echo "</table>";

}
       

function displayBoatLogs ($db) {
/* displays the complete set of boat actions in date order */

    $boattolog = $_POST['boattolog'];
    
    if (!$boattolog) {
?>
        <H2>Display boat log</H2>
        <form method="post" action="admin.php">
        <input name=choice type=hidden value=boatlogs>
        Choose boat: 
        <select name=boattolog>
            <option selected value='All'>All</option>
<?php
        $query = new query("SELECT boat, name FROM people ORDER BY boat");
        while ($query->next_rec()) {
            echo "<option value=\"" . $query->field('name') . "\">" . $query->field('boat') . "</option>\n";
            }
        echo "
        </select>
        <input name=Go type=submit value=Go>
        </form>";
        }
    else {
?>

<H2>List of actions in date order</H2>
    
      <table width="100%" border="1" cellspacing="0" cellpadding="3">
        <tr>
            <td STYLE="font-size: x-small"><b>Time</b></td>
            <td STYLE="font-size: x-small"><b>Name</b></td>
            <td STYLE="font-size: x-small"><b>Investment (&euro;)</b></td>
            <td STYLE="font-size: x-small"><b>Catch</b></td>
            <td STYLE="font-size: x-small"><b>In bank (&euro;)</b></td>
        </tr>
        
<?php 
       $selector = ($boattolog == 'All' ? '' : "WHERE name = '$boattolog'");
            
       $query = new query("SELECT to_char(time, 'DD Mon YYYY, HH24:MI') as date, name, investment, harvest, balance 
                            FROM log $selector ORDER BY time");
       while ($query->next_rec()) {
                  printf("<tr>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   <td STYLE=\"font-size: x-small\">%s</td>
                   </tr>", $query->field("date"), $query->field("name"), $query->field("investment"), 
                           $query->field("harvest"), $query->field("balance"));
           }
        echo "</table>";
    }
}

function displayMsgs($db) {
/* displays the complete set of messages between all boats, in date order */

?>

<H2>List of messages in date order</H2>

      <table width="100%" border="1" cellspacing="0" cellpadding="3">
        <tr>
            <td STYLE="font-size: x-small"><b>Time</b></td>
            <td STYLE="font-size: x-small"><b>Sender</b></td>
            <td STYLE="font-size: x-small"><b>Recipient</b></td>
            <td STYLE="font-size: x-small"><b>Read</b></td>
            <td STYLE="font-size: x-small"><b>Message</b></td>
        </tr>
        
<?php 
       $query = new query("SELECT * FROM msgs ORDER BY timesent");
       while ($query->next_rec()) {
                  printf("<tr>
                    <td STYLE=\"font-size: x-small\">%s</td>
                    <td STYLE=\"font-size: x-small\">%s</td>
                    <td STYLE=\"font-size: x-small\">%s</td>
                    <td STYLE=\"font-size: x-small\">%s</td>
                    <td STYLE=\"font-size: x-small\">%s</td>
                   </tr>", 
                   $query->field("timesent"), $query->field("sender"), $query->field("recipient"), 
                    ($query->field("timeread") ? "Y" : "N"), $query->field("msg"));
           }
        echo "</table>";
}

?>