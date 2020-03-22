<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* displays the main Gone Fishing page and asks the user to specify an investment.
Included by index.php

*/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML lang="en">
<HEAD>
<META HTTP-EQUIV="REFRESH" CONTENT="600">
<TITLE>Gone Fishing!</TITLE>
</HEAD>
<BODY BGCOLOR="#006699" text= "white" link="#FFCC00" vlink="#FFFF00" 
    TOPMARGIN="0" LEFTMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0">

   <!-- top bar -->
<table BORDER="0" CELLSPACING="0" CELLPADDING="5" WIDTH="100%"> 
    <tr  BGCOLOR="#003333" HEIGHT="50">
	<td ALIGN=left VALIGN=middle><font SIZE=5>&nbsp;<b>Gone Fishing!</b></font></td>
	<td ALIGN=right VALIGN=middle><font SIZE=4><?php echo date("g:ia, l j F Y"); ?>&nbsp;</font></td></tr>
    <tr>
	<td ALIGN=left><font SIZE=4 face="arial">
<?php
        switch ($welcome_back) { 
            case "old_user":
                echo "Welcome back, ";
                break;
            case "new_user":
                echo "Welcome, ";
                break;
            default:
                break;
            }
        echo ucfirst($name) . ", sailing in the \"$fisher->boat\"</font>"; 
?>
        </td>
	<td ALIGN=right><font SIZE=2 face="arial">
<?php
        /* display the names of the boats also at sea */
            $query = new query("SELECT * FROM people WHERE logintime IS NOT NULL and name != '$name'");
            $nboats = $query->num_recs();
            $printed_name = 0;
            while ($query->next_rec()) {
                if ($printed_name) {
                    if ($printed_name == $nboats - 1) { echo " and ";} else {echo "; ";};
                    }
                $printed_name++;
                printf(" %s", $query->field("boat"));
                }
            if ($printed_name) {
                echo ($printed_name == 1 ? " is" : " are");
                echo " also at sea.\n";
                }
            else {echo "No one else is at sea.\n";}
            echo "<BR>";
        /* and those not at sea */
            $printed_name = 0;
            $query = new query("SELECT * FROM people WHERE logintime IS NULL");
            $nboats = $query->num_recs();
            while ($query->next_rec()) {
                if ($printed_name) {
                    if ($printed_name == $nboats - 1) { echo " and ";} else {echo "; ";};
                    }
                $printed_name++;
                printf(" %s", $query->field("boat"));
                }
            if ($printed_name) {
                echo ($printed_name == 1 ? " is" : " are");
                echo " tied up in harbour.\n";
                }
            else {echo "No one is in port.\n";}
            echo "<BR><a href=\"ranks.php\">See how the other fishers are doing</a>";
?>
            </font>
        </td>
    </tr>
</table>

<table BORDER="0" CELLSPACING="2" CELLPADDING="1" WIDTH=100%>
<tr ALIGN="left" VALIGN="top">
<!-- left margin -->
	<td WIDTH=20><br></td>
<!-- left middle -->	
       <td>
        <table BORDER="0" CELLSPACING="2" CELLPADDING="5" width=100%>
                <!-- vertical spacer -->
            <tr><td HEIGHT=40><br></td></tr>
                <!-- current state box -->
            <tr ALIGN="left" VALIGN="top">
            <td BGCOLOR="#99CCFF"><font SIZE="2" color="black" face="arial">
            Your bank balance is now &euro; <B><?php echo $fisher->balance; ?></B><p>
<?php
            $query = new query("SELECT max(balance) as maxbalance FROM people WHERE name <> '$name'");
            $query->next_rec();
            $maxbalance = $query->field("maxbalance");
            if ($fisher->balance > $maxbalance) {
                echo "At the moment, you are the fisher with the largest bank balance - congratulations!<p>\n";
                }
            else {
                if ($fisher->balance == $maxbalance) {
                    echo "You are one of the group of richest fishers who all have a balance of &euro; $maxbalance.<p>\n";
                    }
                else {
                    $query = new query("SELECT boat FROM people WHERE balance = '$maxbalance'");
                    $query->next_rec();
                    $richest = $query->field("boat");
                    echo "The balance of the richest fisher ($richest) is &euro; <B>$maxbalance</B><p>\n";
                    }
                }
?>
            The market price of fish today is &euro; <B><?php echo ($sea->marketPrice ? $sea->marketPrice : "--"); ?></B><p>
            The harbour fees per trip are &euro; <B><?php echo $sea->maintenance; ?> </B><p>
            Average amount invested per trip is &euro; <B><?php echo 
            ($log->averageInvestment ? $log->averageInvestment : "--") ?> </B><p>
            Average size of recent catches is <B><?php echo ($log->averageCatchSize ? 
            $log->averageCatchSize : "--") ?> </B><p>
            The number of fish in the lake is <B><?php echo $sea->stock; ?> </B> <p></font>
            </td>
        </tr> 
            <!-- investment box -->
        <tr>
        <td BGCOLOR="#99CCFF" VALIGN=middle>
                <form METHOD="post" ACTION="index.php">
                    <input TYPE=hidden NAME=action VALUE=gofishing>
			<table border=0> <tr><td>
                    <font SIZE="2" color="black" face="arial">
                        <b>Before setting sail, I want to invest </b>&euro; </font>
                    <input TYPE="text" NAME="investment" MAXLENGTH="7" SIZE="7"> <br>
                    <font SIZE="2" color="black" face="arial"><b>in fishing equipment.</b></font>
			</td><td align=right>
                    <input TYPE="submit" NAME="gofishing" VALUE="Go fishing...">
			</td></tr></table>
                </form>
        </td></tr>
        
            <!-- take a holiday option -->
        
        <tr><td BGCOLOR="#006699" VALIGN=middle>
            <font SIZE="2" COLOR="white">
            If you want to withdraw from fishing for a time,<br>  
            to get on with other things, you can 
            <A HREF="index.php?action=logout">take a holiday</A>.
            </font>
        </td>
        </tr>
        
        <!-- vertical spacer -->
        
<tr><td height=20><br></td></tr>
</table>

<!-- ships radio -->
<?php
    /* display the list of messages that have been received */
    include("radio.inc");
?>

	</td>
<!-- centre gulley -->
<td width=40><BR></td>
<!-- right middle -->
	<td>
            <table BORDER="0" CELLSPACING="0" CELLPADDING="5">
                
                <!-- chart -->
                
                <tr><td colspan=4 align=center><img src="chart.php?<?php echo time() ?>"></td></tr>

<?php
        /* display table of recent activity */
        
        $query = new query("SELECT id FROM log WHERE name='$name'");
        if ($query->num_recs()) {
?>
            <tr><td colspan=4><hr></td></tr>
            <tr><td colspan=4 align=center><font face="arial" size="3">
                <b>Log for the <?php echo $fisher->boat ?></b></font>
            </td></tr>
            <tr><td colspan=4><hr></td></tr>
            <tr>
                <td><font size="2"><b>Time</b></font></td>
                <td><font size="2"><b>Investment</b> (&euro;)</font></td>
                <td><font size="2"><b>Catch</b> (fish)</font></td>
                <td><font size="2"><b>In bank</b> (&euro;)</font></td>
            </tr>
        
<?php 
            /* extract the last five records */
            $query = new query("SELECT to_char(time, 'DD Mon, HH24:MI') as date, investment, harvest, balance FROM log 
                              WHERE name = '$name' ORDER BY time DESC LIMIT 5");
            $query->last_rec();
            while ($query->prev_rec()) {
                printf("<tr>
                    <td align=left><font size=2>%s</font></td>
                    <td align=right><font size=2>%s</font></td>
                    <td align=right><font size=2>%s</font></td>
                    <td align=right><font size=2>%s</font></td>
                        </tr>", 
            $query->field("date"),  $query->field("investment"),  $query->field("harvest"),  $query->field("balance"));
                }
      echo "<tr><td colspan=4><hr>";
      }
?>      
        </td></tr>
    </table>
</td>
<!-- right margin -->
	<td WIDTH=20><br></td>
</tr>
</table>
