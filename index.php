<?php

declare(strict_types=1);   // strict typing

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('asciimath.php');
$am = new AMserver();

function tdRend(string $op, string $lexop = ''): void
{
    global $am;
    $safeOp = htmlspecialchars($op);
    echo "\n<tr><td>$safeOp</td><td>$lexop</td><td>{$am->parseMath($op)}</td></tr>";
}
function tdChar(string $letter, bool $upper = false)
{
    global $am;

    $uLetter = $upper ? ucfirst($letter) : '';
    $uRender = $upper ? $am->parseMath(ucfirst($letter)) : '';
    echo "\n<tr><td>$letter</td><td>{$am->parseMath($letter)}</td><td>$uLetter</td><td>$uRender</td></tr>";
}
function startTable(string $title, array $headings)
{
    echo "\n<table style='padding:20px;'><caption><b>$title</b></caption><thead><tr>";
    foreach ($headings as $heading) {
        echo "<th>$heading</th>";
    }
    echo "</thead><tbody>";
}

function endTable()
{
    echo  "\n</tbody></table>";
}

function symbols(): void
{

    startTable("Operation Symbols", ['Type', 'TeX alt', 'See']); {
        tdRend('+');
        tdRend('-');
        tdRend('*', 'cdot');
        tdRend('**', 'ast');
        tdRend('***', 'star');
        tdRend('//', '');
        tdRend('setminus', 'backslash');
        tdRend('xx', 'times');
        tdRend('-:', 'div');
        tdRend('|><', 'times');
        tdRend('><|', 'rtimes');
        tdRend('|><|', 'bowtie');

        tdRend('@', 'circ');
        tdRend('o+', 'oplus');
        tdRend('ox', 'otimes');
        tdRend('o.', 'odot');
        tdRend('sum', '');
        tdRend('prod', '');
        tdRend('^^', 'wedge');
        tdRend('^^^', 'bigwedge');
        tdRend('vv', 'vee');
        tdRend('vvv', 'bigvee');
        tdRend('nn', 'cap');
        tdRend('nnn', 'bigcap');
        tdRend('uu', 'cup');
        tdRend('uuu', 'bigcup');
        tdRend('dag', 'dagger');
        tdRend('ddag', 'ddagger');
        tdRend('hbar');
        tdRend('frown');
    }
    endTable();

    startTable('Miscellaneous Symbols', ['Type', 'Tex alt', 'See']); {
        tdRend('2/3', 'frac{2}{3}');
        tdRend('2^3', '');
        tdRend('sqrt x', '');

        tdRend('root(3)(x)', '');
        tdRend('int', '');

        tdRend('oint', '');
        tdRend('del', 'partial');
        tdRend('grad', 'nabla');
        tdRend('+-', 'pm');
        tdRend('O/', 'emptyset');
        tdRend('oo', 'infty');
        tdRend('aleph', '');

        tdRend(':.', 'therefore');
        tdRend(':\'', 'because');
        tdRend('|...|', '|ldots|');
        tdRend('|cdots|', '');
        tdRend('vdots', '');
        tdRend('ddots', '');
        tdRend('|\ |', '');
        tdRend('|quad|', '');
        tdRend('/_', 'angle');
        tdRend('frown', '');
        tdRend('/_\\', 'triangle');
        tdRend('diamond', '');
        tdRend('square', '');
        tdRend('|__', 'lfloor');
        tdRend('__|', 'rfloor');
        tdRend('|~', 'lceiling');
        tdRend('~|', 'rceiling');
        tdRend('CC', '');
        tdRend('NN', '');
        tdRend('QQ', '');
        tdRend('RR', '');
        tdRend('ZZ', '');


        tdRend('"hi"', 'text(hi)');
    }
    endTable();

    startTable("Relation Symbols", ['Type', 'TeX alt', 'See']); {
        tdRend('=', '');
        tdRend('!=', 'ne');
        tdRend('<', 'lt');
        tdRend('>', 'gt');
        tdRend('<=', 'le');
        tdRend('>=', 'ge');
        tdRend('mlt', 'll');
        tdRend('mgt', 'gg');
        tdRend('-<', 'prec');
        tdRend('-<=', 'preceq');
        tdRend('>-', 'succ');
        tdRend('>-=', 'succeq');
        tdRend('in', '');
        tdRend('!in', 'notin');
        tdRend('sub', 'subset');
        tdRend('!sub');
        tdRend('sup', 'supset');
        tdRend('!sup');
        tdRend('supe', 'supseteq');
        tdRend('!supe');
        tdRend('sube', 'supseteq');
        tdRend('!sube');
        tdRend('-=', 'equiv');
        tdRend('!-=', 'notequiv');
        tdRend('-=', 'equiv');
        tdRend('~=', 'cong');
        tdRend('~~', 'approx');
        tdRend('prop', 'propto');
        tdRend('rightleftharpoons');
    }
    endTable();

    startTable("Logical Symbols", ['Type', 'TeX alt', 'See']); {
        tdRend('and', '');
        tdRend('or', '');
        tdRend('not', 'neg');
        tdRend('=>', 'implies');
        tdRend('if', '');
        tdRend('<=>', 'iff');
        tdRend('AA', 'forall');
        tdRend('EE', 'exists');
        tdRend(':|:');
        tdRend('_|_', 'bot');
        tdRend('TT', 'top');
        tdRend('|--', 'vdash');
        tdRend('|==', 'models');
        tdRend('square');
        tdRend('diamond');
    }
    endTable();

    startTable("Grouping Brackets", ['Type', 'TeX alt', 'See']); {
        tdRend('( x+y )');
        tdRend('[x+y]');
        tdRend('{x+y}');
        tdRend('|:x+y:|');
        tdRend('<x+y>');
        tdRend('{x+y}');
        tdRend('(:x+y:)');
        tdRend('<<x+y>>');
        tdRend('{:x+y)');
        tdRend('(x+y:}');
        tdRend('abs(x)', '');
        tdRend('floor(x)', '');
        tdRend('ceil(x)', '');
        tdRend('norm(vecx)', '');
    }
    endTable();

    startTable("Arrows", ['Type', 'TeX alt', 'See']); {
        tdRend('uarr', 'uparrow');
        tdRend('darr', 'downarrow');
        tdRend('rarr', 'rightarrow');
        tdRend('->', 'to');
        tdRend('>->', 'rightarrowtail');
        tdRend('->>', 'twoheadrightarrow');
        tdRend('>->>', 'twoheadrightarrowtail');
        tdRend('|->', 'mapsto');
        tdRend('larr', 'leftarrow');
        tdRend('harr', 'leftrightarrow');
        tdRend('rArr', 'Rightarrow');
        tdRend('lArr', 'Leftarrow');
        tdRend('hArr', 'Leftrightarrow');
    }
    endTable();

    startTable("Accents", ['Type', 'TeX alt', 'See']); {
        tdRend('hat x', '');
        tdRend('bar x', 'overline x');
        tdRend('ul x', 'underline x');
        tdRend('vec x', '');
        tdRend('tilde x', '');
        tdRend('dot x', '');
        tdRend('ddot x', '');
        tdRend('overset(x)(=)', '');
        tdRend('underset(x)(=)', '');
        tdRend('ubrace(1+2)', 'underbrace(1+2)');
        tdRend('obrace(1+2)', 'overbrace(1+2)');
        tdRend('overarc(AB)', 'overparen(AB)');
        tdRend('color(red)(x)', '');
        tdRend('cancel(x)', '');
    }
    endTable();

    startTable("Greek Letters", ['Type', 'See', 'Type', 'See']); {
        tdChar('alpha');
        tdChar('beta');
        tdChar('gamma', true);
        tdChar('delta', true);
        tdChar('epsilon');
        tdChar('varepsilon');
        tdChar('varepsilon');
        tdChar('zeta');
        tdChar('eta');
        tdChar('theta', true);
        tdChar('vartheta');
        tdChar('iota');
        tdChar('kappa');
        tdChar('lambda', true);
        tdChar('mu');
        tdChar('nu');
        tdChar('xi', true);
        tdChar('pi', true);
        tdChar('rho');
        tdChar('sigma', true);
        tdChar('tau');
        tdChar('upsilon');
        tdChar('phi', true);
        tdChar('varphi');
        tdChar('chi');
        tdChar('psi', true);
        tdChar('omega', true);
    }
    endTable();

    startTable("Font Commands", ['Type', 'TeX alt', 'See']); {
        tdRend('bb "AaBbCc"', 'mathbf "AaBbCc"');
        tdRend('bbb "AaBbCc"', 'mathbb "AaBbCc"');
        tdRend('cc "AaBbCc"', 'mathcal "AaBbCc"');
        tdRend('tt "AaBbCc"', 'mathtt "AaBbCc"');
        tdRend('fr "AaBbCc"', 'mathfrak "AaBbCc"');
        tdRend('sf "AaBbCc"', 'mathsf "AaBbCc"');
        tdRend('sfit "AaBbCc"');
        tdRend('bbsf "AaBbCc"');
        tdRend('bbcc "AaBbCc"');
        tdRend('bbfr "AaBbCc"');
        tdRend('bbit "AaBbCc"');
        tdRend('bbsfit "AaBbCc"');
        tdRend('bold "AaBbCc"');
        tdRend('italic AaBbCc"');
        tdRend('a quad b');
        tdRend('a qquad b');
        tdRend('a enspace b');
        tdRend('a thinspace b');
        tdRend('a mspace(20) b');
    }
    endTable();
}

function SpecialCases(string $case, string $code)
{

    global $am;
    if (strlen($case) > 0)
        echo "\n<p style='line-height:1.5em;'>$case: <code>$code</code> yields {$am->parseMath($code)}</p>";
    else
        echo "\n<p style='line-height:1.5em;'>&nbsp;&nbsp;&nbsp;&nbsp; <code>$code</code> yields {$am->parseMath($code)}</p>";
}


function examples(): void
{
    $examples = <<<EOD
    <div>
    <br>
    <h3>Standard Functions</h3>

    <p>Function names are treated as constants: <br> sin, cos, tan, sec, csc, cot,
        arcsin, arccos, arctan, sinh, cosh, tanh, sech, csch, coth, exp, log, ln,
        det, dim, mod, gcd, lcm, lub, glb, min, max, f, g.</p>

    <br>

    <h3>Special Cases</h3>

    EOD;
    echo "\n", $examples;

    
    echo 'Matrices (mix and match grouping brckets):';
    specialCases('', '[[a,b]]');
    specialCases('', '[(a,b)(c,d)]');
    specialCases('', '((a),(b))');
    specialCases('', '[[a,b],[c,d]]');

    specialCases('Augmented matrices', '[[a,b,|,c],[d,e,|,f]]');
    specialCases('Matrices can be used for layout', '{(2x,+,17y,=,23),(x,-,y,=,5):}');
    specialCases('Complex subscripts', 'lim_(N->oo) sum_(i=0)^N');
    specialCases('Subscripts must come before superscripts', 'int_0^1 f(x)dx');
    specialCases('Derivatives', 'f(x) = dy/dx');
    specialCases('For variables other than x,y,z, or t you will need grouping symbols',    '(dq)/(dp)');

    echo 'OverBraces and underbraces:<br>';
    specialCases('', 'ubrace(1+2+3+4)_("4 terms")');
    specialCases('', 'obrace(1+2+3+4)^("4 terms")');

    echo '<br>Attention: Always try to surround the <code>&gt;</code> and
        <code>&lt;</code> characters with spaces so that the html parser does not
        confuse it with an opening or closing tag!';
    echo "</div>";
}

function amHeader()
{

    $header = <<<EOD
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AsciiMath PHP</title>
    <meta name=viewport content="width=device-width, initial-scale=0.8,
			minimum-scale=0.8, maximum-scale=10">

    <link href="https://fonts.googleapis.com/css2?family=STIX Two Math" rel="stylesheet">

    <style>
        body {
            font-size: 16px;
            font-family: Arial, Helvetica, sans-serif;
            color: rgb(80, 80, 80);
            background-color: rgb(200, 200, 200);
            line-height: 1.3;
        }

        pre {
            word-break: break-all;
            white-space: pre-wrap;
        }

        code,
        pre.code {
            background-color: rgb(230, 230, 230);
            font-size: 0.9em;
            padding: 0.2em 0.4em;
            border-radius: 4px;
            word-break: normal;
        }

        a {
            color: rgb(80, 100, 230);
        }

        mark {
            background-color: rgb(255, 230, 220);
            display: block;
            font-size: 0.9em;
            padding: 0.5em;
            color: rgb(190, 90, 80);
            border-radius: 4px;
        }

        mark a {
            color: rgb(190, 90, 80);
        }

        ol {
            margin-left: 2em;
        }

        li {
            margin-bottom: 1em;
        }

        nav {
            margin-bottom: 2em;
            padding-top: 1.5em;
            border-bottom: 2px solid rgb(150, 50, 50);
            background-color: white;
            position: relative;
        }

        h1 {
            display: inline-block;
            margin-right: 5.5em;
            color: black;
            font-family: "Quicksand", sans-serif;
            font-size: 2em;
            font-weight: 100;
        }

        h2 {
            font-size: 1.2em;
            margin-bottom: 0.4em;
            color: rgb(150, 50, 50);
        }


        h3 {
            font-size: 1em;
            margin-bottom: 0.4em;
        }

        p {
            margin-bottom: 0.8em;
        }

        section {
            margin-bottom: 2em;
        }

        table {
            border-collapse: collapse;
        }

        table td,
        table th {
            padding: 0.2em 0.4em;
            border: 1px solid rgb(200, 200, 200);
        }

        caption {
            min-height: 2.5em
        }


        .leftColumn,
        .rightColumn {
            display: inline-block;
            width: 45%;
            margin-bottom: 0.8em;
        }

        .leftColumn {
            margin-right: 1em;
        }

        .ioArea {
            font-size: 0.9em;
            width: 100%;
            height: 10em;
            border-radius: 4px;
            border: 1px solid rgb(200, 200, 200);
            display: inline-block;
            white-space: nowrap;
            padding: 1em 1em;
            vertical-align: top;
            resize: none;
        }

        textarea.ioArea {
            white-space: normal;
        }

        #demoSource {
            font-family: Consolas, Menlo, Monaco, monospace;
        }

        #forkMe {
            display: block;
            background-image: url(https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png);
            height: 149px;
            width: 149px;
            text-indent: -9999px;
            position: absolute;
            top: 0;
            right: 0;
            z-index: 1;
        }

        #wrapper {
            width: 720px;
            margin: 0 auto;
            background-color: white;
            padding: 0 2em 4em 2em;
            position: relative;
        }

        #links {
            display: inline-block;
            position: absolute;
            right: 0;
            bottom: 0.4em;
        }

        #links a {
            text-decoration: none;
            color: rgb(80, 80, 80);
            display: inline-block;
            margin-left: 1em;
        }

        #links a:hover {
            color: rgb(0, 0, 0);
        }

        #links a:first-of-type {
            margin: 0;
        }

        #syntax table {
            vertical-align: top;
            display: inline-block;
            margin-right: 1em;
            margin-bottom: 2em;
        }

        #grammar {
            font-size: 0.75em;
        }
    </style>
</head>
<body>
EOD;
    echo $header;
}

function tryMe()
{
    $tryMe = <<<EOD

    <script type="module">
        import {
            AMserver
        } from './asciimath.js';
        let am = new AMserver();
        const entryElement = document.getElementById('demoSource');

        // initial math display
        document.getElementById('demoRendering').innerHTML = am.parseMath(entryElement.value);

        // now update on input
        entryElement.addEventListener("input", (event) => {
            document.getElementById('demoRendering').innerHTML = am.parseMath(entryElement.value);
        })
    </script>
EOD;
    echo $tryMe;
}


amHeader();
echo "<div style='clear: both;' />";

tryMe();
echo "<div style='clear: both;' />";


function gettingStarted()
{
    $gettingStarted = <<<'EOD'

<a id="forkMe" href="https://github.com/asciimath/asciimathml">
    Fork me on GitHub
</a>

<div id="wrapper">

    <nav>
        <h1>AsciiMath PHP</h1>

        <div id="links">
            <a href="#about">about</a>
            <a href="#gettingStarted">getting started</a>
            <a href="#syntax">syntax</a>
        </div>
    </nav>

    <section id="about">
        <h2>About</h2>

        <p>AsciiMath is an easy-to-write markup language for mathematics. Try it:</p><br>

        <div class="leftColumn">
            <i>Input:</i>
            <br>
            <textarea class="ioArea" id="demoSource">sum_(i=1)^n i^3=((n(n+1))/2)^2</textarea>
        </div>

        <div class="rightColumn">
            <i>Rendering:</i>
            <br>
            <div class="ioArea" id="demoRendering">
                `sum_(i=1)^n i^3=((n(n+1))/2)^2`
            </div>
        </div>

        <br>
    </section>

    <section id="gettingStarted">
        <h2>Getting Started</h2>

        <ol>
            <li>
                <p>Download <b>AsciiMath.php</b> from <a href='https://github.com/asciimath/asciimathml/tree/master/asciimath-based'>GitHub</a> and include it in your project.</p>
                <pre class="code">
require_once ('asciimath.php');
$am = new AMserver();</pre>

            </li>
            <li>
                <p>Some versions of Chrome don't offer a Math font by default. Include this link in your header: </p>
                <pre class="code">
&lt;link href="https://fonts.googleapis.com/css2?family=STIX Two Math" rel="stylesheet">
</pre>

            </li>
            <li>
                <p>Add mathematics anywhere in your web pages:</p>
                <pre class="code">
$euler = "e^i pi + 1 = 0";
echo "&lt;p>Euler's Identity is {$am->parseMath($euler)}.&lt;/p>";
</pre>

                <p><b>$am->parseMath($formula, $inline)</b> has a second boolean parameter which defaults true.
                    Inline formulas render directly within a sentence. Set false to render block (or display) formulas
                    as centered, standalone paragraphs. This is equivalent to MathJax's \[ and \( brackets.</p>
            </li>
        </ol>


        <section id="syntax">
            <h2>Syntax</h2>

            <p>Most AsciiMath symbols attempt to mimic in text what they look like
                rendered, like <code>oo</code> for `oo`. Many symbols can also be
                displayed using a TeX alternative, but a preceeding backslash is not
                required.</p>
EOD;
    echo $gettingStarted;
}



function appnd($eqn, $comment)
{
    global $am;
    echo "\n<tr><td>$eqn</td><td>{$am->parseMath($eqn)}</td><td>$comment</td></tr>";
}


function examples2()
{

    echo "<div><br><h3>Some Examples</h3>";
    echo "<table>";
    appnd('x^2+y_1+z_12^34', 'subscripts as in TeX, but numbers are treated as a unit');
    appnd('sin^-1(x)', 'function names are treated as constants');
    appnd('d/dxf(x)=lim_(h->0)(f(x+h)-f(x))/h', 'complex subscripts are bracketed, displayed under lim');
    appnd(
        '\\frac{d}{dx}f(x)=\\lim_{h\\to 0}\\frac{f(x+h)-f(x)}{h}',
        'standard LaTeX notation is an alternative'
    );

    appnd(
        'f(x)=sum_(n=0)^oo(f^((n))(a))/(n!)(x-a)^n',
        'f^((n))(a) must be bracketed, else the numerator is only \'a\''
    );

    appnd(
        'f(x)=\\sum_{n=0}^\\infty\\frac{f^{(n)}(a)}{n!}(x-a)^n',
        'standard LaTeX produces a similar result'
    );

    appnd('int_0^1f(x)dx', 'subscripts must come before superscripts');

    appnd('[[a,b],[c,d]]((n),(k))', 'matrices and column vectors are simple to type');
    appnd('x/x={(1,if x!=0),("undefined",if x=0):}', 'piecewise defined functions are based on matrix notation');
    appnd('a//b', 'use //// for inline fractions');

    appnd('(a/b)/(c/d)', 'with brackets, multiple fraction work as expected');
    appnd('a/b/c/d', 'without brackets the parser chooses this particular expression');

    appnd('((a*b))/c', 'only one level of brackets is removed; * gives standard product');
    appnd('sqrt sqrt root3x', 'spaces are optional, only serve to split strings that should not match');
    appnd('<< a,b >> and {:(x,y),(u,v):}', 'angle brackets and invisible brackets');
    appnd('(a,b)={x in RR | a < x < = b}', 'grouping brackets don\'t have to match');
    appnd('abc-123.45^-1.1', 'non-tokens are split into single characters, but decimal numbers are parsed with possible sign');



    appnd('[[a,b,|,c],[d,e,|,f]]', 'augmented matrices');
    appnd('{(2x,+,17y,=,23),(x,-,y,=,5):}', 'Matrices can be used for layout');
    appnd('lim_(N->oo) sum_(i=0)^N', 'Complex subscripts');

    appnd('(dq)/(dp)', 'For variables other than x,y,z, or t you will need grouping symbols');

    echo "</table>";
    echo "</div>";
}


//////////////////////
// pull everything together


amHeader();
echo "<div style='clear: both;' />";

tryMe();
echo "<div style='clear: both;' />";

gettingStarted();
echo "<div style='clear: both;' />";

echo "<div style='clear: both;' />";
symbols();

echo "</div><div style='clear: both;' />";
examples();

echo "</div><div style='clear: both;' />";
examples2();

echo "</section></body></html>";
