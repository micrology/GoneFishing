<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* read and reply to a message.  id of message to read and display is passed in the variable 'msgno' */

include("common.inc");
	
    html_header("Ship's radio");

    echo "<H1>Ship's Radio</H1>";
    
	    session_start();
	    $boat = $_SESSION['boat'];
	    $db = db_open();

    if (isset($_REQUEST['msgno'])) $msgno = $_REQUEST['msgno'];
    else $msgno = 0;
    if ($msgno) {
        $query = new query("SELECT sender, recipient, timesent, msg FROM msgs 
                                WHERE id ='$msgno'");
        if (!$query->next_rec()) $query->error("No records retrieved");        
        $sender = $query->field("sender");
        $time = $query->field("timesent");
        /* replace newlines with HTML paragraph tag */
        $msg = str_replace("\n", "<P>", strip_tags($query->field("msg")));
        $query = new query("SELECT name FROM people WHERE boat = '$boat'");
        if (!$query->next_rec()) $query->error("No records retrieved");
        $name =$query->field("name");
        echo "<form method=post action=\"index.php\">";
        printf("<H3>Message:</H3>from the %s at %s:<P>\n", ($sender ? $sender : "Fishing Authority"), $time);
        echo "<table width=70% border=0><tr><td bgcolor=white><font color=black>\n$msg\n</font>
        </td>";
        
        $query = new query("UPDATE msgs SET timeread=now() WHERE id ='$msgno'");

        echo "<td>
                <input type=hidden name=action value=message>
                <input type=submit name=submit value=\"Return to fishing\">
              </form></tr></table>";
?>
        <table width="100%" border=0>
            <tr><td>
<?php
        if ($sender) { /* you can't reply to the fishing authority (which has a null name) */
            echo "<H3>Reply:</H3>to the $sender";
            
            echo "<form method=post action=$PHP_SELF>
                    <textarea name=msg cols=100 rows=5></textarea>
                    <input type=hidden name=sender value='$sender'>
                    <input type=submit name=submit value=\"Send\">
                </form>";
            }
?>
            </td>
            <td align=right>
            <img src="images/wlp_radio.jpeg" width=252 height=153 alt="The ship's radio">
            </td>
            </tr>
            </table>
<?php
        }
    else {
    		if (isset($_REQUEST['msg'])) $msg = $_REQUEST['msg'];
    		else $msg = "";
        if ($msg) {
            db_write("INSERT INTO msgs (sender, recipient, timesent, timeread, msg) 
                    VALUES('$boat', '$sender', now(), NULL, '$msg')");
            echo "<H3>Message sent to $sender</H3>";
            }
        echo "<P>
            <form method=post action=\"index.php\">
                <input type=hidden name=action value=message>
                <input type=submit value=\"Return to fishing\">
            </form>";
        }
    html_footer();
    
        
    