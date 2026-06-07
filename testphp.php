<?php

declare(strict_types=1);   // strict typing
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$GLOBALS['debugMode'] = true;


function printNice(mixed $elem, $comment = '')
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

function printNiceR(mixed $elem)
{

    $HTML = printNiceHelper($elem);
    return ($HTML);
}

// helper function for printNice()
function printNiceHelper(mixed $elem, $max_level = 10, $print_nice_stack = array(), $HTML = '')
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
        // $elem = str_replace(' ','@',$elem);
        $HTML .= "<b>{$elem}</b>";
        return ($HTML);
    }

    if (is_numeric($elem)) {
        //$HTML .= htmlentities($elem).'<br>';
        $HTML .= strval($elem);
        return ($HTML);
    }

    if (is_array($elem) || is_object($elem)) {
        if (in_array($elem, $print_nice_stack, true)) {
            $HTML .= "<hr /><h1>function {$backtrace[1]['function']}() (line:{$backtrace[1]['line']})</h1>";
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
        $HTML .= "<b>NULL</b>";
    } elseif (0 === $elem) {
        $HTML .= "0";
    } elseif (true === $elem) {
        $HTML .= "<span style ='color:green;'>TRUE</span>";
    } elseif (false === $elem) {
        $HTML .= "<span style ='color:red;'>FALSE</span>";
    } elseif ("" === $elem) {
        $HTML .= "<font color=green>EMPTY STRING</font>";
    } else {
        // $HTML .= str_replace("\n", "<strong><font color=red>*</font></strong><br>\n", $elem);
    }
    return ($HTML);
}


require_once('asciimath.php');

$html = '';
$am = new AMserver();

$html = "";
$html .= "<!DOCTYPE html><html>";
$html .= "<head>";

$html .= '<title>ASCIIMathML test suite</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <!-- the original ASCIIMathML.js for testing -->
    <script type="text/javascript" src="lib/ASCIIMathML.js"></script>

    <style type="text/css">
        table {
            font-family: Times;
        }

        table {
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }

        @font-face {
           font-family: "STIX-Two-Math";
           src: url("./lib/STIX2Math.otf") format("opentype");
        }
        math {
            font-family: STIX-Two-Math;
            font-size: larger;
        }    
    </style>';



$html .= "</head>";
$html .= "<body>\n";
$html .= "<h2><a href='http://localhost/asciimathml-ts/testphp.php'>PHP</a>  <a href='http://localhost/asciimathml-ts/testts'>TS</a>  </h2>";

$html .= '<table>';
foreach (['Plaintext', 'ASCIIMathML.js', 'asciimath.ts', 'comment'] as $title) {
    $html .= "<th>$title</th>";
}

testSuite();
$html .= '</table>';
$html .= "</body>";
$html .= "</html>";
echo $html;

function appnd(string $str, string $comment = '')
{
    global $html, $am;
    $result = $am->parseMath($str);
    $neutered = str_replace('<', '&lt;', $result);
    $html .= "<tr><td>{$str}</td
    ><td>`{$str}`</td>
    <td>{$result}</td>
    <td style='max-width:300px;'>$neutered</td></tr>";
}

function testAMNode(): string
{
    $node = new AMNode('div');
    $node->appendChild(new AMNode('p'));
    $node->appendChild(new AMNode('span'));
    $node->appendChild(new AMNode('#text', 'this is a test'));
    $node2 = new AMNode('page');
    $node2->appendChild($node);
    return htmlentities($node2->flatten());
}






function testSuite()
{

        appnd ('cancel abc');
    appnd('cancel (x/y)');


    // appnd('x^2+y_1+z_12^34', 'subscripts as in TeX, but numbers are treated as a unit');
    // appnd('sin^-1(x)', 'function names are treated as constants');
    // appnd('d/dxf(x)=lim_(h->0)(f(x+h)-f(x))/h', 'complex subscripts are bracketed, displayed under lim');
    // appnd(
    //     '\\frac{d}{dx}f(x)=\\lim_{h\\to 0}\\frac{f(x+h)-f(x)}{h}',
    //     'standard LaTeX notation is an alternative'
    // );

    // appnd(
    //     'f(x)=sum_(n=0)^oo(f^((n))(a))/(n!)(x-a)^n',
    //     'f^((n))(a) must be bracketed, else the numerator is only \'a\''
    // );

    // appnd( 'f(x)=\\sum_{n=0}^\\infty\\frac{f^{(n)}(a)}{n!}(x-a)^n',
    //     'standard LaTeX produces a similar result'
    // );

    // appnd('int_0^1f(x)dx', 'subscripts must come before superscripts');

    // appnd('[[a,b],[c,d]]((n),(k))', 'matrices and column vectors are simple to type');
    // appnd('x/x={(1,if x!=0),("undefined",if x=0):}', 'piecewise defined functions are based on matrix notation');
    // appnd('a//b', 'use //// for inline fractions');

    // appnd('(a/b)/(c/d)', 'with brackets, multiple fraction work as expected');
    // appnd('a/b/c/d', 'without brackets the parser chooses this particular expression');

    // appnd('((a*b))/c', 'only one level of brackets is removed; * gives standard product');
    // appnd('sqrt sqrt root3x', 'spaces are optional, only serve to split strings that should not match');
    // appnd('<< a,b >> and {:(x,y),(u,v):}', 'angle brackets and invisible brackets');
    // appnd('(a,b]={x in RR | a &lt; x &lt;= b}', 'grouping brackets don\'t have to match');
    // appnd('abc-123.45^-1.1', 'non-tokens are split into single characters, but decimal numbers are parsed with possible sign');


    // appnd('a');
    // appnd('ab');
    // appnd('bold(a)');
    // appnd(' a  bold(b) c');
    // appnd(' a  bold b c');
    // appnd('hat(a)');
    // appnd('c thinspace d ');
    // appnd('a mspace(5)b mspace(1em)c thinspace thinspace d ');


    // appnd('"a"b');



    // appnd('bb abb b');
    // appnd('bb " bb " bb c bb(c)');
    // appnd('sf " sf " sf c sf(c)');
    // appnd('sfit " sfit " sfit c sfit(c)');
    // appnd('bbsf " bbsf " bbsf c bbsf(c)');
    // appnd('bbb " bbb " bbb c bbb(c)');
    // appnd('bbcc " bbcc " bbcc c');
    // appnd('tt " tt " tt c tt(c) ');
    // appnd('fr " fr " fr c fr(c) ');
    // appnd('bbfr " bbfr " bbfr c bbfr(c)');
    // appnd('bbit " bbit " bbit c bbit(c)');
    // appnd('bbsfit " bbsfit " bbsfit c bbsfit(c)');
    // appnd('bold " bold " bold c bold(c)');

    // appnd('a color(red) b color(black) c');
    // return;
    /*
    appnd('cancel (x/y)');
    appnd('cancel (x/y)');
    appnd('cancel (x) /y');
    appnd('cancel ( (x+1) /y');
    appnd('ul m + ul k + ul j + ul x');
    appnd('[[a,b]]');
    appnd('(f^{[n]})');
    appnd('"abc"');
    appnd('a b c, a,b,c');

    appnd('"abc"');
    appnd('a b c, a,b,c');
    appnd('(a)');
    appnd('a+b');
    appnd('(a b)');
    appnd('alpha  beta  gamma');
    appnd('NN');
    appnd('NN ZZ');
    appnd('quad a quad b qquadc');
    appnd('a NN alpha ZZ');
    appnd('a + b - c * d xx e');
    appnd('-200-100 - 50  -a-b');

    appnd('"abc 01239"');
    appnd('"abc 01239 $%*"');
    appnd('bold ("abc 01239 $%*")');
    appnd('bold "$%* abc 01239 $%*"');
    appnd('italic ("abc 01239 $%*")');
    appnd('italic "$%* abc 01239 $%*"');
    appnd('bold italic ("abc 01239 $%*")');
    appnd('bold italic "$%* abc 01239 $%*"');

    appnd('bold abc');
    appnd('bold(abc)');
    // appnd(`bold(abc)`)

    appnd('abc 01239 $%*"');
    appnd('bold "abc 01239 $%*"');
    appnd('bold bold "abc 01239 $%*"');
    appnd('tan x');
    appnd('tan (x) tan (xy)');
    appnd('bold tan (xy)');
    appnd('bold (tan (xy))');
    appnd('hat(x)');
    appnd('hat(x))');
    appnd('hat(x) hat(x)');
    appnd('bold hat (x)');
    appnd('bold abs (x)');
    appnd('sqrt log(x)');
    appnd('bold sqrt log(x)');
    appnd('bold (x_2)');
    appnd('bold x_2');
    appnd('int x');
    appnd('int (x)');
    appnd('int (x_2) y z');
    appnd('bold (x_2) y z');
    appnd('bold (x_2) x_2 bold (x_2)');
    appnd('[[a,b,c,d]]');
    appnd('bold( [[a,b,c,d]] )');
    appnd('bold( [[a,b,c,d]] ) ');
    appnd('a/b');
    appnd('a/b+c');
    appnd('a+b/b');
    appnd('(a+b)/c');
    appnd('a/(b+c)');
    appnd('a/((b+c))');
    
    appnd('a^b');
    appnd('a^b+c');
    appnd('a+b^b');
    appnd('(a+b)^c');
    appnd('a^(b+c)');
    appnd('a^((b+c))');
    appnd('(a+b)/b');
    
    appnd('overset x =');
    appnd('overset x (=)');
    appnd('overset (x) =');
    appnd('overset(x+b)(=)');
    appnd('underset(x)(=)');
    appnd('frac{2}{3}');
    appnd('id(red)(x)');
    appnd('color(red)(x)');
    
    
    appnd('{a,b,c,d}');
    appnd('(a,b,c,d)');
    appnd('[a,b,c,d]');
    appnd('[[ a,b,c,d ]]');
    appnd('([ a,b,c,d ])');
    appnd('{[ a,b,c,d ]}');
    appnd('[[a,b]]');
    appnd('[[a,b][c,d]]');
    
    appnd('sum_(i=1)^n i^3=((n(n+1))/2)^2');
    appnd('[[a,b],[c,d]]');
    
    appnd('[(a,b),(c,d)]');
    appnd('((a),(b))');
    appnd('([a],[b])');
    appnd('(  a,b )');
    appnd('');   // empty
    appnd(' ');
    appnd(' [, ,] ');

    appnd('[]');
    appnd('a ');
    appnd('a/');
    appnd('frac frac $');
    appnd('sin sin a sin');
    appnd('tilde tilde a tilde sin');
    appnd(']');
    appnd('( ] )');
    appnd('[[a %');

    appnd('4 -: 2');
    appnd('a -: b');
    appnd('a divide b');

*/
}
