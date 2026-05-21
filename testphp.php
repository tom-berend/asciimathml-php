<?php
declare(strict_types=1);   // strict typing
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$GLOBALS['debugMode'] = true;

function printNice($elem, $comment = '')
{

    if (!$GLOBALS['debugMode']) {
        return;
    } // debugging isn't on


    $debug = debug_backtrace();
    $HTML = '<br><b>printNice:</b> ' . $debug[0]['file'] . '(' . $debug[0]['line'] . ')';
    if (isset($debug[1]['file'])) {
        $HTML .= '. from ' . $debug[1]['file'] . '(' . $debug[1]['line'] . ')';
    }

    // if (isset($debug[2]['file'])) {
    //     $HTML .= '. from ' . $debug[2]['file'] . '(' . $debug[2]['line'] . ')';
    // }

    $HTML .= "<span style='color:blue;'>$comment</span> ";
    $HTML .= printNiceHelper($elem);
    echo $HTML;
}

// printNice utility for debugging

function printNiceR($elem)
{

    $HTML = printNiceHelper($elem);
    return ($HTML);
}

// helper function for printNice()
function printNiceHelper($elem, $max_level = 10, $print_nice_stack = array(), $HTML = '')
{

    $MAX_LEVEL = 5;

    // show where we were called from
    $backtrace = debug_backtrace(); // if no title, then show who called us
    if ('printNice' !== $backtrace[1]['function'] and 'printNiceHelper' !== $backtrace[1]['function']) {
        if (isset($backtrace[1]['class'])) {
            $HTML .= "<hr /><h1>class {$backtrace[1]['class']}, function {$backtrace[1]['function']}() (line:{$backtrace[1]['line']})</h1>";
        }
    }

    if (is_string($elem)) {
        //$HTML .= htmlentities($elem).'<br>';
        $HTML .= $elem;
        return ($HTML);
    }

    if (is_array($elem) || is_object($elem)) {
        if (in_array($elem, $print_nice_stack, true)) {
            $HTML .= "<hr /><h1>class {$backtrace[1]['class']}, function {$backtrace[1]['function']}() (line:{$backtrace[1]['line']})</h1>";
            return ($HTML);
        }
        if ($max_level < 1) {
            //print_r(debug_backtrace());
            //die;
            $HTML .= "<FONT COLOR=RED>MAX STACK LEVEL OF $MAX_LEVEL EXCEEDED</FONT>";
            return ($HTML);
        }

        $print_nice_stack[] = &$elem;
        $max_level--;
        $HTML .= "<table border=1 cellspacing=0 cellpadding=3 width=100%>";
        if (is_array($elem)) {
            // $HTML .= '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>ARRAY</font></strong></td></tr>';
        } else {
            $HTML .= '<tr><td colspan=2 style="background-color:#333333;"><strong>';
            $HTML .= '<font color=white>OBJECT Type: ' . get_class($elem) . '</font></strong></td></tr>';
        }
        $color = 0;
        foreach ($elem as $k => $v) {
            if ($max_level % 2) {
                $rgb = ($color++ % 2) ? "#888888" : "#BBBBBB";
            } else {
                $rgb = ($color++ % 2) ? "#8888BB" : "#BBBBFF";
            }
            $HTML .= '<tr><td valign="top" style="width:40px;background-color:' . $rgb . ';">';
            $HTML .= '<strong>' . $k . "</strong></td><td>";
            $HTML .= printNiceHelper($v, $max_level, $print_nice_stack);

            $HTML .= "</td></tr>";
        }

        $HTML .= "</table>";
        return ($HTML);
    }
    if (null === $elem) {
        $HTML .= "<font color=green>NULL</font>";
    } elseif (0 === $elem) {
        $HTML .= "0";
    } elseif (true === $elem) {
        $HTML .= "<font color=green>TRUE</font>";
    } elseif (false === $elem) {
        $HTML .= "<font color=green>FALSE</font>";
    } elseif ("" === $elem) {
        $HTML .= "<font color=green>EMPTY STRING</font>";
    } else {
        $HTML .= str_replace("\n", "<strong><font color=red>*</font></strong><br>\n", $elem);
    }
    return ($HTML);
}


require_once('asciimath.php');