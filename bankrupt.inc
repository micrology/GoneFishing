<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* declare the user is bankrupt

included from index.php

*/

    /* restore starting balance, so that the player can login again to start again */
    db_write("UPDATE people SET balance=10000, logintime = NULL WHERE name='$name'");
 
    $query = new query("SELECT type, email FROM people WHERE name='$name'");
    $query->next_rec();
    if ($query->field('type') == 'agent') {
        /* send an email to the agent's owner */
        mail($query->field('email'), "Gone Fishing! status report", "Your agent, $name, has lost all its money and has become bankrupt.
        However, it has been restarted with a new bank balance of 10,000 euro.");
        }
    else {
        /* human player, so display problem on screen */
        html_header("Gone Fishing!");   
?>

<table width="100%" border="0" cellspacing="0" cellpadding="10">
  <tr>
    <td><H2>Bankrupt!</H2></td>
  </tr>
  <tr>
    <td>
     <img src="images/fish.jpg" width="508" height="340">
    </td>
    <td>
    We regret that you no longer have any money to pay for your boat.
    You may return to fishing with a new supply of money, by re-registering yourself with the Fishing Authority, at<p>
    <?php
    
    $url_array = parse_url($_SERVER['REQUEST_URI']);
    $url = "http://" . $_SERVER['SERVER_NAME'] . dirname($url_array["path"]) . "/";
            echo "<a href=\"$url\">$url</a>";
?>
    </td>
  </tr>
</table>
<?php

exit;

    }
