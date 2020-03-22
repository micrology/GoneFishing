<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* write and send a message.  */

session_start();
$boat = $_SESSION['boat'];

include("common.inc");

	
    html_header("Ship's radio");

    echo "<H1>Ship's Radio</H1>
            <img src=\"images/telephone.jpg\" width=183 height=130 alt=\"Telephone\">";
    
    $db = db_open();
    
    if (!isset($_POST['submit'])) {
        $query = new query("SELECT boat FROM people WHERE boat != '$boat' ORDER BY boat");
        if ($query->num_recs()) {
            echo "<form method=post action=" . $_SERVER['PHP_SELF'] . ">
                    Send a message to
                    <select name=recipient>
                    <option selected value=''>Select...</option>\n
                    <option value=all>Everyone </option>\n";
            while ($query->next_rec()) {
                printf("<option>%s</option>\n", $query->field("boat"));
                }
            echo "</select><p>
                <textarea name=msg cols=100 rows=5></textarea>
                <input type=submit name=submit value=\"Send\">
                </form>";
            }
        else {
            echo "<H1>No one else around to send a message to.</H1>";
            }
        }
    else {
				$msg = $_POST['msg'];
				$recipient = $_POST['recipient'];
        if (!$recipient) alert("Please select someone to send the message to.");
        else {
            if ($msg) {
                    db_write("INSERT INTO msgs (sender, recipient, timesent, timeread, msg) 
                            VALUES('$boat', '$recipient', now(), NULL, '$msg')");
                    if ($recipient == "all") {
                        echo "<H3>Message broadcast to everyone</H3>";
                        }
                    else {
                        echo "<H3>Message sent to $recipient</H3>";
                        }
                }
            echo "<P>
                <form method=post action=\"index.php\">
                    <input type=hidden name=action value=message>
                    <input type=submit value=\"Return to fishing\">
                </form>";
            }
        }
    html_footer();
    