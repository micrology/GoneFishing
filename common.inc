<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* functions common to all or many PHP files */

include("defines.inc");

function html_header($title = "Gone Fishing!", $bgcolor = "#006699", $link = "#FFFFFF", $text = "#FFFFFF") {
    /* insert standard HTML page header */

    echo    "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
    echo    "<HTML lang=\"en\">\n";
    echo    "<HEAD>\n";
		echo 		"<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
    echo    "<META HTTP-EQUIV=\"expires\" CONTENT=\"now\">\n";
    echo    "<META HTTP-EQUIV=\"pragma\" CONTENT=\"no-cache\">\n";
    echo    "<TITLE>$title</TITLE>\n";
    echo    "</HEAD>\n";
    echo    "<BODY bgcolor=\"$bgcolor\" text=\"$text\" link=\"$link\" vlink=\"$link\" alink=\"$link\">\n";

}

function html_footer() {
/* display standard HTML footer */

    echo "</BODY>\n</HTML>";

}

function alert($message) {
/* put up a modal dialog box with the message */

    html_header("Alert");
    echo "<SCRIPT> alert('$message'); history.go(-1)</SCRIPT>";
    html_footer();
}

function debug($var, $val) {
/* displays the value of the variable, but only if the $debug global is true */

    global $debug;
    
    if ($debug) printf("<font color=yellow>%s = %s </font><p>\n", $var, $val);
}

//==================================database interface====================================//

function db_open($database = "fishing") {
/* open the database and trap errors.  Returns  link to database */

    global $db;
    
    $db = @pg_connect("dbname=$database");
    if (!$db) db_error("Cannot connect to database: $database");
    return $db;
}

function db_write($sql) {
/* carry out an UPDATE or INSERT operation.  Assumes db is already connected. */

    global $db;
    
    $result = @pg_exec($db, $sql);
    if (!$result) db_error("", $sql);
}
    
function db_error($errormsg="", $sql="") {
/* display an error message and then die */

    global $admin_mail, $db;
    
    if (empty($errormsg)) {
        $errormsg = pg_errormessage($db);
        }
    html_header("Database Error", "#FFFFFF", "#000000", "#000000");
    echo "<font color=red><H1>Database error</H1>A database error has been encountered:</font><P>\n";
    echo $errormsg;
    if ($sql) echo "<BR> (while evaluating: $sql)";
    mail($admin_mail, "Gone Fishing! database error",
        "Database error:\nevaluating $sql\nError is: $errormsg");
    html_footer();
    exit;
}

class Query {

/* class to send and store result of a query to the database.  Assumes the db is already
   connected */

    public $cursor,
        $result,
        $row,
        $nrows,
        $lastSQL;
        
    function error($msg) {
        db_error($msg, $this->lastSQL);
    }
    
    function query($query) {
    /* never returns if there is an error (prints error message and dies) */

        global $db;

        $this->lastSQL = $query;
        $this->result = @pg_exec($db, $query);
        if (!$this->result) $this->error("Bad query");
        $this->cursor = 0;
        $this->nrows = pg_numrows($this->result);
    }
    
    function seek($loc) {
    /* positions the cursor at $loc, so that the next record to be read is the one at $loc */
    
        $this->cursor = $loc;
    }

    function next_rec() {
    /* extracts an associative array holding the next record retrieved from the DB.
    Returns the array or 0 if no more */
    
        if ($this->cursor >= $this->nrows) return 0;
        $this->row = pg_fetch_array($this->result, $this->cursor++);
        return $this->row;
    }

    function num_recs() {
    /* returns the number of records retrieved from the DB */
    
        return $this->nrows;
    }

    function last_rec() {
    /* set the cursor to the last row in the retrieved array */
    
        $this->cursor = $this->nrows;
    }
    
    function prev_rec() {
    /* returns the row before the one previously retrieved, or 0 if there isn't one */
    
        if (!$this->cursor) return 0;
        $this->row = pg_fetch_array($this->result, --$this->cursor);
        return $this->row;
    }
    
    function field($fieldname) {
    /* return the value in the given field in the current record */
    
        return $this->row[$fieldname];
    }

}
