<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* display a list of players sorted by bank balance */

include("common.inc");
session_start();
$boat = $_SESSION['boat'];

    html_header("Fisher bank balances");

    echo "<H1>Fisher bank balances</H1>";
    
    $db = db_open();
?>
<table border=0>
    <tr><td width=20><br></td><td valign=top>
      <table border="0" cellspacing="0" cellpadding="3">
        <tr> 
            <td colspan=4><hr></td>
        </tr>
        <tr>
            <td><font face=arial size=2>Boat</font></td>
            <td align=center><font face=arial size=2>Date registered</font></td>
            <td align=center><font face=arial size=2>Last fishing trip</font></td>
            <td><font face=arial size=2>Balance</font></td>
        </tr>
        <tr>
            <td colspan=4><hr></td>
        </tr>
<?php 
       $query = new query("SELECT boat, to_char(createdtime, 'DD Mon YYYY') as registered, 
                                to_char(lastoptime, 'DD Mon YYYY, HH24:MI') as fishtime, balance 
                                FROM people ORDER BY balance DESC");
       while ($query->next_rec()) {
           printf("<tr>
                   <td><font face=arial size=2 %s>%s</font></td>
                   <td><font face=arial size=2>%s</font></td>
                   <td><font face=arial size=2>%s</font></td>
                   <td align=right><font face=arial size=2>%s</font></td>
                   </tr>", 
                   ($boat == $query->field("boat") ? "color=yellow" : ""),
                   $query->field("boat"), $query->field("registered"),
                   $query->field("fishtime"), $query->field("balance"));
           }
      echo "</tr><tr><td colspan=4><hr></td></table>";
?>
</td>
<td width=450 align=right>
<img src="images/shark.jpg" width=362 height=318 align=top>
</td></tr>
<tr>
	<td><br></td>
	<td align=right>
		<form method=post action="index.php">
        <input type=submit name=submit value="Return to fishing">
    </form>
   </td>
</tr>
</table>    
</body>
</html>
          
      

