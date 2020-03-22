<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* write and send a message to all players  (sent by administration, but appears to come from OFFISH).  */

include("common.inc");

    html_header("Gone Fishing! Administration");

    echo "<H1>Gone Fishing! Administration</H1>";
    
    if ($_POST['password'] <> "firma") {  /* rather low security password feature! */
        alert("Bad password");
        exit;
        }
    if (isset($_POST['submit']) and $_POST['submit'] == "Send") {
    		$msg = $_POST['msg'];
        if ($msg) {
                $db = db_open();
                db_write("INSERT INTO msgs (sender, recipient, timesent, timeread, msg) 
                                VALUES(NULL, 'All', now(), NULL, '$msg')");
                echo "<H3>Message has been broadcast to all</H3>";
                }
        echo "<P>
                <form method=get action=\"admin.html\">
                    <input type=submit value=\"Return to main page\">
                </form>";
        }
    else {
        echo "<form method=post action=\"msgall.php\">
            Enter the message to send to all players:<p>
            <textarea name=msg cols=100 rows=5></textarea>
            <input type=hidden name=password value=\"firma\">
            <input type=submit name=submit value=\"Send\">
            </form>";
    }
    html_footer();
    