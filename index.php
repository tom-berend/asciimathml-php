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
    echo "<tr><td>$op</td><td>$lexop</td><td>{$am->parseMath($op)}</td>  <td>{$am->parseMath($lexop)}</td> </tr>";
}
function tdChar(string $letter, bool $upper = false)
{
    global $am;

    $uLetter = $upper ? ucfirst($letter) : '';
    $uRender = $upper ? $am->parseMath(ucfirst($letter)) : '';
    echo "<tr><td>$letter</td><td>{$am->parseMath($letter)}</td><td>$uLetter</td><td>$uRender</td></tr>";
}
function startTable(string $title, array $headings)
{
    //startTable('Miscellaneous Symbols', ['Type','Tex alt', 'See']);
    echo "<table><caption><b>$title</caption><thead><tr>";
    foreach ($headings as $heading) {
        echo "<th>$heading</th>";
    }
    echo "</thead><tbody>";
}

function endTable()
{
    echo  "</tbody></table>";
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AsciiMath Example</title>
    <meta name=viewport content="width=device-width, initial-scale=0.8,
			minimum-scale=0.8, maximum-scale=10">
    <style>
        @font-face {
            font-family: "STIX-Two-Math";
            src: url("./lib/STIX2Math.otf") format("opentype");
            font-display: block;
        }

        math {
            font-family: STIX-Two-Math;
            font-size: larger;
        }
    </style>
    <link rel=stylesheet href=lib/screen.css>

</head>

<body>

    <p id="insertMathHere"></p>

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

</body>

</html>


<!--  -->

<!doctype html>
</head>

<body>

    <a id="forkMe" href="https://github.com/asciimath/asciimathml">
        Fork me on GitHub
    </a>

    <div id="wrapper">

        <nav>
            <h1>AsciiMath</h1>

            <div id="links">
                <a href="#about">about</a>
                <a href="#gettingStarted">getting started</a>
                <a href="#syntax">syntax</a>
            </div>
        </nav>

        <article>
            <section id="about">
                <h2>About</h2>

                <p>AsciiMath is an easy-to-write markup language for mathematics. Try it out:</p>

                <div class="leftColumn">
                    <label for="demoSource">Input:</label>
                    <br />

                    <textarea class="ioArea" id="demoSource">sum_(i=1)^n i^3=((n(n+1))/2)^2</textarea>
                </div>

                <div class="rightColumn">
                    <label for="demoRendering">Rendering:</label>
                    <br />

                    <div class="ioArea" id="demoRendering">
                        `sum_(i=1)^n i^3=((n(n+1))/2)^2`
                    </div>
                </div>

                <p>This page is being rendered using the AsciiMathML PHP library. </p>
            </section>

            <section id="gettingStarted">
                <h2>Getting Started</h2>

                <ol>
                    <li>
                        <p>Download the AsciiMath.php file from <a href=''>WHERE?</a> and include it in your <project class=""></project>to render your formulas.

                        <pre class="code">
&lt;style>
    @font-face {
        font-family: "STIX-Two-Math";
        src: url("./lib/STIX2Math.otf") format("opentype");
        font-display: block;
    }

    math {
        font-family: STIX-Two-Math;
        font-size: larger;
    }
&lt;/style>
</pre>

                        <p>Text in your HTML enclosed in <code>`</code> (backticks) will now get
                            rendered as a math formula. The math delimiters can also be customized.
                            Check out the <a href=https://mathjax.org />MathJax</a> website for more
                            information!</p>
                    </li>
                    <li>
                        <p>Load the AsciiMath javascript file
                            (<a href="http://git.io/X84VQQ">get it on GitHub</a>)
                            in either the <code>head</code> or the <code>body</code>
                            tag of your website like this:</p>

                        <p>
                            <code>&lt;script
                                src=&quot;ASCIIMathML.js&quot;&gt;&lt;/script&gt;</code>
                        </p>

                        <p>This file contains JavaScript to convert AsciiMath
                            notation and (some) LaTeX to Presentation MathML. The conversion
                            is done while the HTML page loads.</p>

                        <p>Almost all recent browsers support MathML out of the box,
                            though not all features may work, and math might display
                            differently in different browsers.
                            For more information on browser compatibility, see the
                            <a
                                href="https://developer.mozilla.org/en-US/docs/Web/MathML/Element/math#browser_compatibility">MDN
                                web docs</a>.
                        </p>

                    </li>
                </ol>
            </section>

            <section id="syntax">
                <h2>Syntax</h2>

                <p>Most AsciiMath symbols attempt to mimic in text what they look like
                    rendered, like <code>oo</code> for `oo`. Many symbols can also be
                    displayed using a TeX alternative, but a preceeding backslash is not
                    required.</p>

                <?php

                startTable("Operation Symbols", ['Type', 'TeX alt', 'See<']);
                tdRend('+');
                tdRend('-');
                tdRend('*', 'cdot');
                tdRend('**', 'ast');
                tdRend('***', 'star');
                tdRend('//', '');
                tdRend('\\', 'backslash<br />setminus');
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
                endTable();
                startTable('Miscellaneous Symbols', ['Type', 'Tex alt', 'See']);
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
                endTable();
                startTable("Relation Symbols", ['Type', 'TeX alt', 'See']);
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
                tdRend('sup', 'supset');
                tdRend('sube', 'subseteq');
                tdRend('supe', 'supseteq');
                tdRend('-=', 'equiv');
                tdRend('~=', 'cong');
                tdRend('~~', 'approx');
                tdRend('prop', 'propto');
                endTable();
                startTable("Logical Symbols", ['Type', 'TeX alt', 'See']);
                tdRend('and', '');
                tdRend('or', '');
                tdRend('not', 'neg');
                tdRend('=>', 'implies');
                tdRend('if', '');
                tdRend('<=>', 'iff');
                tdRend('AA', 'forall');
                tdRend('EE', 'exists');
                tdRend('_|_', 'bot');
                tdRend('TT', 'top');
                tdRend('|--', 'vdash');
                tdRend('|==', 'models');

                endTable();
                startTable("UNKNOWN", ['Type', 'TeX alt', 'See']);

                tdRend('mbox (a)');
                tdRend('stackrel (a)');
                tdRend('rightleftharpoons');
                tdRend('log a');
                tdRend('ln a');
                tdRend('hbar');
                tdRend('square');
                tdRend('diamond');
                tdRend('frown');
                tdRend('a quad b');
                tdRend('a qquad b');
                tdRend('a enspace b');
                tdRend('a thinspace b');
                tdRend('a mspace(20) b');
                tdRend('id');
                tdRend('class');
                tdRend('dx');
                tdRend('dy');
                tdRend('dz');
                tdRend('dt');
                tdRend('');
                tdRend('');



                endTable();
                startTable("Grouping Brackets", ['Type', 'TeX alt', 'See']);

                tdRend('(', '');
                tdRend(')', '');
                tdRend('[', '');
                tdRend(']', '');
                tdRend('{', '');
                tdRend('}', '');
                tdRend('(:', 'langle');
                tdRend(':)', 'rangle');
                tdRend('<<', '');
                tdRend('>>', '');
                tdRend('{: x )', '');
                tdRend('( x :}', '');
                tdRend('abs(x)', '');
                tdRend('floor(x)', '');
                tdRend('ceil(x)', '');
                tdRend('norm(vecx)', '');

                endTable();
                startTable("Arrows", ['Type', 'TeX alt', 'See']);

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

                endTable();
                startTable("Accents", ['Type', 'TeX alt', 'See<']);

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

                endTable();
                startTable("Greek Letters", ['Type', 'See', 'Type', 'See']);

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

                endTable();
                startTable("Font Commands", ['Type', 'TeX alt', 'See']);

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

                endTable()

                ?>
                </tbody>
                </table>
                <br />
                <h3>Standard Functions</h3>

                <p>sin, cos, tan, sec, csc, cot,
                    arcsin, arccos, arctan, sinh, cosh, tanh, sech, csch, coth, exp, log, ln,
                    det, dim, mod, gcd, lcm, lub, glb, min, max, f, g.</p>

                <br />

                <h3>Special Cases</h3>

                <p>Matrices: <code>[[a,b],[c,d]]</code> yields to `[[a,b],[c,d]]`</p>

                <p>Column vectors: <code>((a),(b))</code> yields to `((a),(b))`</p>

                <p>Augmented matrices: <code>[[a,b,|,c],[d,e,|,f]]</code> yields to `[[a,b,|,c],[d,e,|,f]]`</p>

                <p>Matrices can be used for layout:
                    <code>{(2x,+,17y,=,23),(x,-,y,=,5):}</code> yields
                    `{(2x,+,17y,=,23),(x,-,y,=,5):}`
                </p>

                <p>Complex subscripts: <code>lim_(N->oo) sum_(i=0)^N</code> yields to `lim_(N->oo) sum_(i=0)^N`</p>

                <p>Subscripts must come before superscripts:
                    <code>int_0^1 f(x)dx</code> yields to `int_0^1 f(x)dx`
                </p>

                <p>Derivatives: <code> f'(x) = dy/dx</code> yields `f'(x) = dy/dx`<br />
                    For variables other than x,y,z, or t you will need grouping symbols:
                    <code> (dq)/(dp)</code> for `(dq)/(dp)`
                </p>

                <p>Overbraces and underbraces:
                    <code>ubrace(1+2+3+4)_("4 terms")</code> yields `ubrace(1+2+3+4)_("4 terms")`.<br />
                    <code>obrace(1+2+3+4)^("4 terms")</code> yields `obrace(1+2+3+4)^("4 terms")`.
                </p>

                <p>Attention: Always try to surround the <code>&gt;</code> and
                    <code>&lt;</code> characters with spaces so that the html parser does not
                    confuse it with an opening or closing tag!
                </p>
                <br />


                <h3>The Grammar</h3>

                <p>Here is a definition of the grammar used to parse AsciiMath expressions.
                    In the Backus-Naur form given below, the letter on the left of the
                    <code>::=</code> represents a category of symbols that could be one
                    of the possible sequences of symbols listed on the right.
                    The vertical bar <code>|</code> separates the alternatives.
                </p>

                <pre id=grammar>
v ::= [A-Za-z] | greek letters | numbers | other constant symbols
u ::= sqrt | text | bb | other unary symbols for font commands
b ::= frac | root | stackrel | other binary symbols
l ::= ( | [ | { | (: | {: | other left brackets
r ::= ) | ] | } | :) | :} | other right brackets
S ::= v | lEr | uS | bSS             Simple expression
I ::= S_S | S^S | S_S^S | S          Intermediate expression
E ::= IE | I/I                       Expression
</pre>

            </section>

        </article>
    </div>


</body>

</html>