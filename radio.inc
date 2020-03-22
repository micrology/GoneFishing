<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* shows the panel on the Fishing page that lists messages received.
Included by fishing.inc

*/
	global $boat;
    /* get the last 5 messages */
    $query = new query("SELECT id, sender, recipient, to_char(timesent, 'HH24:MI on DD Mon YYYY') as senttime, 
                            timeread, msg 
                            FROM msgs 
                            WHERE recipient='$boat' OR (recipient = 'All' AND (sender IS NULL OR sender != '$boat')) 
                            ORDER BY timesent DESC LIMIT 5");
    echo "<table border=0 width=\"100%\" cellpadding=0 cellspacing=0>
                <tr><td  valign=center bgcolor= \"#003333\">\n
                <font color=\"white\" size=\"4\" face=\"arial\"><B>Ship's radio</B> messages</font></td></tr>
                <tr><td bgcolor=\"#003333\"><hr></td></tr>
                <tr><td bgcolor=\"#003333\"><font color=\"white\" size=1>";
    $msgcnt = 0;
    $query->last_rec();
    while ($query->prev_rec()) {
        $msgcnt++;
        /* extract the first 70 or so characters of the message (removing newlines and multiple spaces) */
        $msg_start = preg_replace("/[\s]+/", " ", $query->field("msg"));
        if (strlen($msg_start) > 66) {
            $msg_start = substr($msg_start, 0, 66) . "...";
            }
        printf("<a href=\"msgread.php?msgno=%s\"> %s at %s %s</a><BR>&nbsp;&nbsp;<font size=1>%s</font><BR\n>",
                $query->field("id"),
                ($query->field("sender") ? $query->field("sender") : "OFFISH"),   /* The Fishing Authority has no name */
                $query->field("senttime"), 
                ($query->field("timeread") ? "" : "[NEW]"),
                $msg_start);
        }
    while ($msgcnt++ <= 5) {
        echo "<BR>\n";
        }
    echo "</font></td></tr>\n<tr><td HEIGHT=40 ALIGN=center VALIGN=center>
            <form method=post action=\"msgwrite.php\">
                <input type=submit name=msgno value='Send a radio message'>
            </form>
        </table>";
?>