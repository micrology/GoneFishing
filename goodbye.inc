<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* 
bids goodbye to the user who has chosen to take a holiday on the main fishing page
Included by index.php

*/
    html_header("Goodbye!");
?>

<table width="100%" border="0" cellspacing="0" cellpadding="10">
  <tr>
    <td>
        <H2>Goodbye!</H2>
    </td>
  </tr>
  <tr>
    <td>
        <img src="images/fish.jpg" width="508" height="340">
    </td>
    <td>
        You may return to fishing any time, at<P>
<?php
        
		    $url_array = parse_url($_SERVER['REQUEST_URI']);
		    $url = "http://" . $_SERVER['SERVER_NAME'] . dirname($url_array["path"]) . "/";
        echo "<a href=\"$url\"><b>$url</b></a>";
?>
        <p>
        Meanwhile, your bank balance will be safe!
    </td>
  </tr>
</table>
