<?php

/*==================================================
            GONE FISHING
            v1.0 April 2001
            Nigel Gilbert
            n.gilbert@soc.surrey.ac.uk
====================================================*/

/* Draw a plot of the sea stocks. Uses the phplot package.

    referenced as an image by fish.inc*/

include("common.inc");	
include("phplot.php");

    db_open();
    $query = new query("SELECT price, stock FROM sea ORDER BY time DESC LIMIT 50");
    $nvalues = $query->num_recs();
    if ($nvalues > 1) { /* only plot points if there are 2 or more */
        $query->last_rec();
        $count = 0;
        while ($query->prev_rec()) { 
            $stock[$count] = array("", $count, $query->field("stock"));
                    // price is scaled so that max price (10) is comparable with max stock (200,000)
            $price[$count] = array("", $count, 20000 * $query->field("price"));
            $count++;
            }
	$graph = new PHPlot(300,300);
    $graph->SetPrintImage(0);
	$graph->SetDataType("data-data");  //Must be first thing
	$graph->SetBrowserCache(0);
    $graph->SetFileFormat("PNG");
	$graph->SetNumVertTicks(1);
    $graph->SetNumHorizTicks(1);
    $graph->SetPrecisionY(0);
    $graph->SetLineWidth("1");
	$graph->SetDrawYGrid("0"); // 1 = true
	$graph->SetXLabel("Time");
    $graph->SetBackgroundColor(array(0,102,153)); //can use rgb values or "name" values
    $graph->SetTextColor("white");
    $graph->SetGridColor("white");
    $graph->SetLightGridColor(array(0,102,153));
    $graph->SetTickColor("white");
    $graph->SetLabelColor("white");
    $graph->SetTitleColor("white"); // Can be array or name

        /* display fish population as an area graph */
	$graph->SetPlotType("area");
    $graph->SetDataValues($stock);
	$graph->SetYLabel("Fish (red)/\nPrice (green)");
	$graph->SetTitle("Fish population\nMarket price");
	$graph->SetDataColors(
		array("red", "green"),  		//Data Colors
		array("black")				//Border Colors
            );  
    $graph->SetPlotAreaWorld(0, 0, 50, 200000);
    $graph->DrawGraph();
   
    /* now draw the price change as a line graph */

    $graph->SetDataValues($price);
    $graph->SetPlotType("lines");
    $graph->SetDataColors(array("green"), array("red"));
    $graph->DrawGraph();

    $graph->PrintImage();
    }

?>

