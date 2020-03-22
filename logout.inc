<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* 
bids goodbye to the user who has exceeded his or her idletime
Included by index.php

*/

    global $idleTime;
    
    $idlemins = round($idleTime / 60);
    $url_array = parse_url($_SERVER['REQUEST_URI']);
    $url = "http://" . $_SERVER['SERVER_NAME'] . dirname($url_array["path"]) . "/";

/* send an email to player to say they have been logged out */

    $query = new query("SELECT email FROM people WHERE name = '$name'");
    $query->next_rec();
    $email = $query->field('email');
    if ($email) {
        mail($email, "Gone Fishing!  Your boat has returned to port",
        "You have been inactive for $idlemins minutes and so your boat has returned to port. 
        You may go back to fishing any time, at <$url>.
        
        The Fishing Authority");
        }
    html_header("Au revoir!");
?>        
    
<table width="100%" border="0" cellspacing="0" cellpadding="10">
    <tr  BGCOLOR="#003333" HEIGHT="50">
	<td ALIGN=left VALIGN=middle><font SIZE=5>&nbsp;<b>Gone Fishing!</b></font></td>
	<td ALIGN=right VALIGN=middle><font SIZE=4><?php echo date("g:ia, l j F Y"); ?>&nbsp;</font></td></tr>
  <tr>
    <td>
        <H2>Au revoir!</H2>
    </td>
  </tr>
  <tr>
    <td>
        <img src="images/fishing-boat-red-buoys.jpg" width="384" height="512">
    </td>
    <td>
<?php
        echo "You have been inactive for $idlemins minutes and so your boat has returned to port. <p>
        You may go back to fishing any time, at <b>";

        echo "<a href=\"$url\">$url</a></b>";
?>
    </td>
  </tr>
</table>
</body>
</html>
