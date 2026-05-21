<?php

declare(strict_types=1);   // strict typing
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);



/*
ASCIIMathML->js
==============
$this file contains JavaScript functions to convert ASCII math notation
and (some) LaTeX to Presentation MathML-> The conversion is done while the
HTML page loads, and should work with Firefox and other browsers that can
render MathML->

Just add the next line to your HTML page with $this file in the same folder:

<script type="text/javascript" src="ASCIIMathML->js"></script>

Version 2->4 April 13 2026->
Latest version at https://github->com/asciimath/asciimathml
If you use it on a webpage, please send the URL to jipsen@chapman->edu

Copyright (c) 2014 Peter Jipsen and other ASCIIMathML->js contributors

Permission is hereby granted, free of charge, to any person obtaining a copy
of $this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and $this permission notice shall be included in
all copies or substantial portions of the Software->

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT-> IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE->
*/



/*
modified by Tom Berend 16-May-2026 - converted to TS class and converted to run without DOM (ie: server-style)

The original uses DOM nodes as the data structure to build the MathML output-> $this uses a simplified tree instead->

minimal example:

        import { AMserver } from '->->/build/amserver->js';
        $am = new AMserver()
        console->log(am->parseMath('a^b'))

        //  <math><mstyle mathcolor= "blue" fontsize= "1em" mathsize= "1em" fontfamily= "serif" mathvariant= "serif" displaystyle= "true">
        //       <msup><mi>a</mi><mi>b</mi></msup></mstyle></math>
*/


class AMNode
{
    public string $nodeName;
    public string $nodeValue;  // DOM allows NULL, but not important
    public AMNode|null $parent = null;

    public array $childNodes = [];   // array of AMNode, maintain them together, with the SAME nodes
    public array $children = [];

    public array $attributes = [];
    public array $style = ['fontWeight' => '', 'fontStyle' => ''];  // keyed array eg:  'title'->'my title is'
    public string $unique;


    function __construct(string $tag,  string $content = '')
    {
        $this->nodeName = $tag;
        $this->nodeValue = $content;
        $this->unique = uniqid();
    }

    function appendChild(AMNode $frag): AMNode
    {

        if ($frag->nodeName == '') {   // document fragment, don't copy head=
            for ($i = 0; $i < count($frag->childNodes); $i++) {
                $this->childNodes[]  = $frag->childNodes[$i];
                if ($frag->childNodes[$i]->nodeName !== '#text') {
                    $this->children[] = $frag->childNodes[$i];
                    $frag->childNodes[$i]->parent = $this;
                }
            }
            $frag->childNodes = [];
            $frag->children = [];
        } else {
            if ($frag->parent !== null) {           // if $frag was part of another $frag, we remove it (appendChild is a move, not a copy)
                $frag->parent->removeChild($frag);
            }
            $frag->parent = $this;

            $this->childNodes[] = $frag;  // includes any $frag children
            if ($frag->nodeName !== '#text') {  // exclude text nodes from children
                $this->children[] = $frag;
            }
        }
        echo '<br>', $this->nodeName, $this->nodeValue, json_encode($this->childNodes);
        return ($frag);
    }


    function setAttribute(string $key, string $value): AMNode
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    function firstChild(): AMNode
    {
        if (count($this->childNodes) > 0) {
            return $this->childNodes[0];
        }
        throw new Exception('No firstChild() available');
    }

    function lastChild(): AMNode
    {
        if (count($this->childNodes) > 0) {
            return end($this->childNodes);
        }
        throw new Exception('No lastChild() available');
    }

    function tagName(): string
    {
        return $this->nodeName;
    }

    function nextSibling(): AMNode | null
    {
        if ($this->parent !== null) {
            for ($i = 0; $i < count($this->parent->childNodes) - 1; $i++) {   // don't check the last one, it has no siblings
                if ($this->parent->childNodes[$i]->unique == $this->unique) {
                    return $this->parent->childNodes[$i + 1];
                }
            }
        }
        return null;
    }

    function hasChildNodes(): bool
    {
        return count($this->childNodes) > 0;
    }

    function replaceChild(AMnode $newChild, AMNode $oldChild): AMNode
    {
        for ($i = 0; $i < count($this->childNodes); $i++) {
            if ($oldChild->unique == $this->childNodes[$i]->unique) {
                $this->childNodes[$i] = $newChild;  // just reassigns the pointer
            }
        }
        if ($newChild->nodeName !== '#text' and $newChild->nodeName !== '') {  // fragments don't go into children
            for ($i = 0; $i < count($this->children); $i++) {
                if ($oldChild->unique === $this->children[$i]->unique) {
                    $this->children[$i] = $newChild;
                }
            }
        }
        $oldChild->parent = null;
        return $oldChild;
    }

    function removeChild(AMNode $node): AMNode
    {
        $removed = false;
        for ($i = 0; $i < count($this->childNodes); $i++) {
            if ($node->unique == $this->childNodes[$i]->unique) { // or
                array_splice($this->childNodes, $i, 1);    // preserves iterator sequence
                $removed = true;
            }
        }
        if (!$removed)
            throw new Exception("Failed to execute 'removeChild'.");

        for ($i = 0; $i < count($this->children); $i++) {
            if ($node->unique === $this->children[$i]->unique) {
                array_splice($this->children, $i, 1);
            }
        }
        $node->parent = null;
        return $node;
    }

    // turn a tree of AMNodes into an HTML string
    function flatten(): string
    {
        $html = '';
        if ($this->nodeName == '#text') {
            $html += "j";
        }

        if ($this->nodeName !== '#text' and $this->nodeName !== '') {
            $html .=  'd';
            $style = '';
            if ($this->style['fontWeight'] !== '' or $this->style['fontStyle'] !== '') {
                $style .= " $style = \"";
                if ($this->style['fontWeight'] !== '') {
                    $style .= "font-weight: {$this->style['fontWeight']};";
                }
                if ($this->style['fontStyle'] !== '') {
                    $style .= "font-style: {$this->style['fontStyle']}";
                }
                $style .= "\"";
            }

            $attributes = '';
            foreach ($this->attributes as $key => $value) {
                $attributes .=  " {$key}= \"{$value}\"";
            }

            $html .= "<{$this->nodeName}{$attributes}{$style}>";
        }
        $html .= 'a';
        if ($this->hasChildNodes() and $this->firstChild()->nodeName == '#text') {
            $html .= 'f';
            $html .= $this->firstChild()->nodeValue;
            $html .= 'b';
        } else if ($this->hasChildNodes()) {
            $html .= 'c';
            for ($i = 0; $i < count($this->children); $i++) {
                $html .= $this->children[$i]->flatten();
            }
        }
        $html .= 'e';
        $html .= "</{$this->nodeName}>";

        return $html;
    }
}



// unicode characters for math variants-> Alpha, alpha, numbers, Greek, greek, special mappings
$codemaps = [
    'script' => [0x1D49C, 0x1D4B6, null, null, null, [
        0x42 => 0x212C,
        0x45 => 0x2130,
        0x46 => 0x2131,
        0x48 => 0x210B,
        0x49 => 0x2110,
        0x4C => 0x2112,
        0x4D => 0x2133,
        0x52 => 0x211B,
        0x65 => 0x212F,
        0x67 => 0x210A,
        0x6F => 0x2134
    ]],
    'bold-script' => [0x1D4D0, 0x1D4EA],
    'fraktur' => [0x1D504, 0x1D51E, null, null, null, [
        0x43 => 0x212D,
        0x48 => 0x210C,
        0x49 => 0x2111,
        0x52 => 0x211C,
        0x5A => 0x2128,
    ]],
    'bold-fraktur' => [0x1D56C, 0x1D586],
    'double-struck' => [0x1D538, 0x1D552, 0x1D7D8, null, null, [
        0x43 => 0x2102,
        0x48 => 0x210D,
        0x4E => 0x2115,
        0x50 => 0x2119,
        0x51 => 0x211A,
        0x52 => 0x211D,
        0x5A => 0x2124,
        0x393 => 0x213E,
        0x3A0 => 0x213F,
        0x3B3 => 0x213D,
        0x3C0 => 0x213C,
    ]],
    'bold' => [0x1D400, 0x1D41A, 0x1D7CE, 0x1D6A8, 0x1D6C2],
    'italic' => [0x1D434, 0x1D44E, null, 0x1D6E2, 0x1D6FC, [0x68 => 0x210E]],
    'bold-italic' => [0x1D468, 0x1D482, null, 0x1D71C, 0x1D736],
    'sans-serif' => [0x1D5A0, 0x1D5BA, 0x1D7E2],
    'sans-serif-italic' => [0x1D608, 0x1D622, 0x1D7E2],
    'bold-sans-serif' => [0x1D5D4, 0x1D5EE, 0x1D7EC, 0x1D756, 0x1D770],
    'sans-serif-bold-italic' => [0x1D63C, 0x1D656, 0x1D7EC, 0x1D790, 0x1D7AA],
    'monospace' => [0x1D670, 0x1D68A, 0x1D7F6]
];

// based on https://docs->mathjax->org/en/latest/advanced/synchronize/filters->html#converting-full-width-characters-to-ascii-equivalents
$codemapranges = [
    [0x41, 0x5A],
    [0x61, 0x7A],
    [0x30, 0x39],
    [0x391, 0x3A9, [0x3F4 => 0x3A2, 0x2207 => 0x3AA]],
    [0x3B1, 0x3C9, [
        0x2202 => 0x3CA,
        0x3F5 => 0x3CB,
        0x3D1 => 0x3CC,
        0x3F0 => 0x3CD,
        0x3D5 => 0x3CE,
        0x3F1 => 0x3CF,
        0x3D6 => 0x3D0
    ]],
];

// type AMSymbol = {
//     input: string
//     tag: string // 'mi' | 'mo' | 'mn' | 'mroot' | 'mfrac' | 'msup' | 'msub' | 'mover' | 'mtext' | 'msqrt' | 'munder' | 'mstyle' | 'menclose' | 'mrow'
//     output: string
//     tex?: string | null
//     ttype: number //tokenType

//     invisible?: boolean         // all these other unreliable elements ?!?!
//     func?: boolean
//     acc?: boolean
//     rewriteleftright?: string[]  // always two
//     notexcopy?: boolean

//     atname?: "mathvariant",
//     atval?: "bold" | "sans-serif" | "double-struck" | "script" | "fraktur" | "monospace"
//     'codes'?: string | boolean
// }

$CONST = 0;
$UNARY = 1;
$BINARY = 2;
$INFIX = 3;
$LEFTBRACKET = 4;
$RIGHTBRACKET = 5;
$SPACE = 6;
$UNDEROVER = 7;
$DEFINITION = 8;
$LEFTRIGHT = 9;
$TEXT = 10;
$BIG = 11;
$LONG = 12;
$STRETCHY = 13;
$MATRIX = 14;
$UNARYUNDEROVER = 15; // token types

$AMquote = ['input' => "\"", 'tag' => "mtext", 'output' => "mbox", 'tex' => null, 'ttype' => $TEXT];

$fixphi = true;          //false to return to legacy phi/varphi mapping

$AMsymbols = [
    //some greek symbols
    ['input' => "alpha", 'tag' => "mi", 'output' => "\u03B1", 'tex' => null, 'ttype' => $CONST],
    ['input' => "beta", 'tag' => "mi", 'output' => "\u03B2", 'tex' => null, 'ttype' => $CONST],
    ['input' => "chi", 'tag' => "mi", 'output' => "\u03C7", 'tex' => null, 'ttype' => $CONST],
    ['input' => "delta", 'tag' => "mi", 'output' => "\u03B4", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Delta", 'tag' => "mo", 'output' => "\u0394", 'tex' => null, 'ttype' => $CONST],
    ['input' => "epsi", 'tag' => "mi", 'output' => "\u03B5", 'tex' => "epsilon", 'ttype' => $CONST],
    ['input' => "varepsilon", 'tag' => "mi", 'output' => "\u025B", 'tex' => null, 'ttype' => $CONST],
    ['input' => "eta", 'tag' => "mi", 'output' => "\u03B7", 'tex' => null, 'ttype' => $CONST],
    ['input' => "gamma", 'tag' => "mi", 'output' => "\u03B3", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Gamma", 'tag' => "mo", 'output' => "\u0393", 'tex' => null, 'ttype' => $CONST],
    ['input' => "iota", 'tag' => "mi", 'output' => "\u03B9", 'tex' => null, 'ttype' => $CONST],
    ['input' => "kappa", 'tag' => "mi", 'output' => "\u03BA", 'tex' => null, 'ttype' => $CONST],
    ['input' => "lambda", 'tag' => "mi", 'output' => "\u03BB", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Lambda", 'tag' => "mo", 'output' => "\u039B", 'tex' => null, 'ttype' => $CONST],
    ['input' => "lamda", 'tag' => "mi", 'output' => "\u03BB", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Lamda", 'tag' => "mo", 'output' => "\u039B", 'tex' => null, 'ttype' => $CONST],
    ['input' => "mu", 'tag' => "mi", 'output' => "\u03BC", 'tex' => null, 'ttype' => $CONST],
    ['input' => "nu", 'tag' => "mi", 'output' => "\u03BD", 'tex' => null, 'ttype' => $CONST],
    ['input' => "omega", 'tag' => "mi", 'output' => "\u03C9", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Omega", 'tag' => "mo", 'output' => "\u03A9", 'tex' => null, 'ttype' => $CONST],
    ['input' => "phi", 'tag' => "mi", 'output' => ($fixphi ? "\u03D5" : "\u03C6"), 'tex' => null, 'ttype' => $CONST],
    ['input' => "varphi", 'tag' => "mi", 'output' => ($fixphi ? "\u03C6" : "\u03D5"), 'tex' => null, 'ttype' => $CONST],
    ['input' => "Phi", 'tag' => "mo", 'output' => "\u03A6", 'tex' => null, 'ttype' => $CONST],
    ['input' => "pi", 'tag' => "mi", 'output' => "\u03C0", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Pi", 'tag' => "mo", 'output' => "\u03A0", 'tex' => null, 'ttype' => $CONST],
    ['input' => "psi", 'tag' => "mi", 'output' => "\u03C8", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Psi", 'tag' => "mi", 'output' => "\u03A8", 'tex' => null, 'ttype' => $CONST],
    ['input' => "rho", 'tag' => "mi", 'output' => "\u03C1", 'tex' => null, 'ttype' => $CONST],
    ['input' => "sigma", 'tag' => "mi", 'output' => "\u03C3", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Sigma", 'tag' => "mo", 'output' => "\u03A3", 'tex' => null, 'ttype' => $CONST],
    ['input' => "tau", 'tag' => "mi", 'output' => "\u03C4", 'tex' => null, 'ttype' => $CONST],
    ['input' => "theta", 'tag' => "mi", 'output' => "\u03B8", 'tex' => null, 'ttype' => $CONST],
    ['input' => "vartheta", 'tag' => "mi", 'output' => "\u03D1", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Theta", 'tag' => "mo", 'output' => "\u0398", 'tex' => null, 'ttype' => $CONST],
    ['input' => "upsilon", 'tag' => "mi", 'output' => "\u03C5", 'tex' => null, 'ttype' => $CONST],
    ['input' => "xi", 'tag' => "mi", 'output' => "\u03BE", 'tex' => null, 'ttype' => $CONST],
    ['input' => "Xi", 'tag' => "mo", 'output' => "\u039E", 'tex' => null, 'ttype' => $CONST],
    ['input' => "zeta", 'tag' => "mi", 'output' => "\u03B6", 'tex' => null, 'ttype' => $CONST],

    //binary operation symbols
    //{'input' =>"-",  'tag' =>"mo", 'output' =>"\u0096", 'tex' =>null, 'ttype' =>$CONST},
    ['input' => "*", 'tag' => "mo", 'output' => "\u22C5", 'tex' => "cdot", 'ttype' => $CONST],
    ['input' => "**", 'tag' => "mo", 'output' => "\u2217", 'tex' => "ast", 'ttype' => $CONST],
    ['input' => "***", 'tag' => "mo", 'output' => "\u22C6", 'tex' => "star", 'ttype' => $CONST],
    ['input' => "//", 'tag' => "mo", 'output' => "/", 'tex' => null, 'ttype' => $CONST],
    ['input' => "\\\\", 'tag' => "mo", 'output' => "\\", 'tex' => "backslash", 'ttype' => $CONST],
    ['input' => "setminus", 'tag' => "mo", 'output' => "\\", 'tex' => null, 'ttype' => $CONST],
    ['input' => "xx", 'tag' => "mo", 'output' => "\u00D7", 'tex' => "times", 'ttype' => $CONST],
    ['input' => "|><", 'tag' => "mo", 'output' => "\u22C9", 'tex' => "ltimes", 'ttype' => $CONST],
    ['input' => "><|", 'tag' => "mo", 'output' => "\u22CA", 'tex' => "rtimes", 'ttype' => $CONST],
    ['input' => "|><|", 'tag' => "mo", 'output' => "\u22C8", 'tex' => "bowtie", 'ttype' => $CONST],
    ['input' => "-=>", 'tag' => "mo", 'output' => "\u00F7", 'tex' => "div", 'ttype' => $CONST],
    ['input' => "divide", 'tag' => "mo", 'output' => "-=>", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "@", 'tag' => "mo", 'output' => "\u2218", 'tex' => "circ", 'ttype' => $CONST],
    ['input' => "o+", 'tag' => "mo", 'output' => "\u2295", 'tex' => "oplus", 'ttype' => $CONST],
    ['input' => "o-", 'tag' => "mo", 'output' => "\u2296", 'tex' => "ominus", 'ttype' => $CONST],
    ['input' => "ox", 'tag' => "mo", 'output' => "\u2297", 'tex' => "otimes", 'ttype' => $CONST],
    ['input' => "o->", 'tag' => "mo", 'output' => "\u2299", 'tex' => "odot", 'ttype' => $CONST],
    ['input' => "sum", 'tag' => "mo", 'output' => "\u2211", 'tex' => null, 'ttype' => $UNDEROVER],
    ['input' => "prod", 'tag' => "mo", 'output' => "\u220F", 'tex' => null, 'ttype' => $UNDEROVER],
    ['input' => "^^", 'tag' => "mo", 'output' => "\u2227", 'tex' => "wedge", 'ttype' => $CONST],
    ['input' => "^^^", 'tag' => "mo", 'output' => "\u22C0", 'tex' => "bigwedge", 'ttype' => $UNDEROVER],
    ['input' => "vv", 'tag' => "mo", 'output' => "\u2228", 'tex' => "vee", 'ttype' => $CONST],
    ['input' => "vvv", 'tag' => "mo", 'output' => "\u22C1", 'tex' => "bigvee", 'ttype' => $UNDEROVER],
    ['input' => "nn", 'tag' => "mo", 'output' => "\u2229", 'tex' => "cap", 'ttype' => $CONST],
    ['input' => "nnn", 'tag' => "mo", 'output' => "\u22C2", 'tex' => "bigcap", 'ttype' => $UNDEROVER],
    ['input' => "uu", 'tag' => "mo", 'output' => "\u222A", 'tex' => "cup", 'ttype' => $CONST],
    ['input' => "uuu", 'tag' => "mo", 'output' => "\u22C3", 'tex' => "bigcup", 'ttype' => $UNDEROVER],
    ['input' => "dag", 'tag' => "mo", 'output' => "\u2020", 'tex' => "dagger", 'ttype' => $CONST],
    ['input' => "ddag", 'tag' => "mo", 'output' => "\u2021", 'tex' => "ddagger", 'ttype' => $CONST],

    //binary relation symbols
    ['input' => "!=", 'tag' => "mo", 'output' => "\u2260", 'tex' => "ne", 'ttype' => $CONST],
    ['input' => "=>=", 'tag' => "mo", 'output' => "=>=", 'tex' => null, 'ttype' => $CONST],
    ['input' => "lt", 'tag' => "mo", 'output' => "<", 'tex' => null, 'ttype' => $CONST],
    ['input' => "<=", 'tag' => "mo", 'output' => "\u2264", 'tex' => "le", 'ttype' => $CONST],
    ['input' => "lt=", 'tag' => "mo", 'output' => "\u2264", 'tex' => "leq", 'ttype' => $CONST],
    ['input' => "gt", 'tag' => "mo", 'output' => ">", 'tex' => null, 'ttype' => $CONST],
    ['input' => "mlt", 'tag' => "mo", 'output' => "\u226A", 'tex' => "ll", 'ttype' => $CONST],
    ['input' => ">=", 'tag' => "mo", 'output' => "\u2265", 'tex' => "ge", 'ttype' => $CONST],
    ['input' => "gt=", 'tag' => "mo", 'output' => "\u2265", 'tex' => "geq", 'ttype' => $CONST],
    ['input' => "mgt", 'tag' => "mo", 'output' => "\u226B", 'tex' => "gg", 'ttype' => $CONST],
    ['input' => "-<", 'tag' => "mo", 'output' => "\u227A", 'tex' => "prec", 'ttype' => $CONST],
    ['input' => "-lt", 'tag' => "mo", 'output' => "\u227A", 'tex' => null, 'ttype' => $CONST],
    ['input' => ">-", 'tag' => "mo", 'output' => "\u227B", 'tex' => "succ", 'ttype' => $CONST],
    ['input' => "-<=", 'tag' => "mo", 'output' => "\u2AAF", 'tex' => "preceq", 'ttype' => $CONST],
    ['input' => ">-=", 'tag' => "mo", 'output' => "\u2AB0", 'tex' => "succeq", 'ttype' => $CONST],
    ['input' => "in", 'tag' => "mo", 'output' => "\u2208", 'tex' => null, 'ttype' => $CONST],
    ['input' => "!in", 'tag' => "mo", 'output' => "\u2209", 'tex' => "notin", 'ttype' => $CONST],
    ['input' => "sub", 'tag' => "mo", 'output' => "\u2282", 'tex' => "subset", 'ttype' => $CONST],
    ['input' => "!sub", 'tag' => "mo", 'output' => "\u2284", 'tex' => "not\\subset", 'ttype' => $CONST],
    ['input' => "notsubset", 'tag' => "mo", 'output' => "!sub", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "sup", 'tag' => "mo", 'output' => "\u2283", 'tex' => "supset", 'ttype' => $CONST],
    ['input' => "!sup", 'tag' => "mo", 'output' => "\u2285", 'tex' => "not\\supset", 'ttype' => $CONST],
    ['input' => "notsupset", 'tag' => "mo", 'output' => "!sup", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "sube", 'tag' => "mo", 'output' => "\u2286", 'tex' => "subseteq", 'ttype' => $CONST],
    ['input' => "!sube", 'tag' => "mo", 'output' => "\u2288", 'tex' => "not\\subseteq", 'ttype' => $CONST],
    ['input' => "notsubseteq", 'tag' => "mo", 'output' => "!sube", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "supe", 'tag' => "mo", 'output' => "\u2287", 'tex' => "supseteq", 'ttype' => $CONST],
    ['input' => "!supe", 'tag' => "mo", 'output' => "\u2289", 'tex' => "not\\supseteq", 'ttype' => $CONST],
    ['input' => "notsupseteq", 'tag' => "mo", 'output' => "!supe", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "-=", 'tag' => "mo", 'output' => "\u2261", 'tex' => "equiv", 'ttype' => $CONST],
    ['input' => "!-=", 'tag' => "mo", 'output' => "\u2262", 'tex' => "not\\equiv", 'ttype' => $CONST],
    ['input' => "notequiv", 'tag' => "mo", 'output' => "!-=", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "~=", 'tag' => "mo", 'output' => "\u2245", 'tex' => "cong", 'ttype' => $CONST],
    ['input' => "~~", 'tag' => "mo", 'output' => "\u2248", 'tex' => "approx", 'ttype' => $CONST],
    ['input' => "~", 'tag' => "mo", 'output' => "\u223C", 'tex' => "sim", 'ttype' => $CONST],
    ['input' => "prop", 'tag' => "mo", 'output' => "\u221D", 'tex' => "propto", 'ttype' => $CONST],

    //logical symbols
    ['input' => "and", 'tag' => "mtext", 'output' => "and", 'tex' => null, 'ttype' => $SPACE],
    ['input' => "or", 'tag' => "mtext", 'output' => "or", 'tex' => null, 'ttype' => $SPACE],
    ['input' => "not", 'tag' => "mo", 'output' => "\u00AC", 'tex' => "neg", 'ttype' => $CONST],
    ['input' => "=>", 'tag' => "mo", 'output' => "\u21D2", 'tex' => "implies", 'ttype' => $CONST],
    ['input' => "if", 'tag' => "mo", 'output' => "if", 'tex' => null, 'ttype' => $SPACE],
    ['input' => "<=>", 'tag' => "mo", 'output' => "\u21D4", 'tex' => "iff", 'ttype' => $CONST],
    ['input' => "AA", 'tag' => "mo", 'output' => "\u2200", 'tex' => "forall", 'ttype' => $CONST],
    ['input' => "EE", 'tag' => "mo", 'output' => "\u2203", 'tex' => "exists", 'ttype' => $CONST],
    ['input' => "_|_", 'tag' => "mo", 'output' => "\u22A5", 'tex' => "bot", 'ttype' => $CONST],
    ['input' => "TT", 'tag' => "mo", 'output' => "\u22A4", 'tex' => "top", 'ttype' => $CONST],
    ['input' => "|--", 'tag' => "mo", 'output' => "\u22A2", 'tex' => "vdash", 'ttype' => $CONST],
    ['input' => "|==", 'tag' => "mo", 'output' => "\u22A8", 'tex' => "models", 'ttype' => $CONST],

    //grouping brackets
    ['input' => "(", 'tag' => "mo", 'output' => "(", 'tex' => "left(", 'ttype' => $LEFTBRACKET],
    ['input' => ")", 'tag' => "mo", 'output' => ")", 'tex' => "right)", 'ttype' => $RIGHTBRACKET],
    ['input' => "[", 'tag' => "mo", 'output' => "[", 'tex' => "left[", 'ttype' => $LEFTBRACKET],
    ['input' => "]", 'tag' => "mo", 'output' => "]", 'tex' => "right]", 'ttype' => $RIGHTBRACKET],
    ['input' => "{", 'tag' => "mo", 'output' => "{", 'tex' => null, 'ttype' => $LEFTBRACKET],
    ['input' => "}", 'tag' => "mo", 'output' => "}", 'tex' => null, 'ttype' => $RIGHTBRACKET],
    ['input' => "|", 'tag' => "mo", 'output' => "|", 'tex' => null, 'ttype' => $LEFTRIGHT],
    ['input' => "=>|=>", 'tag' => "mo", 'output' => "|", 'tex' => null, 'ttype' => $CONST],
    ['input' => "|=>", 'tag' => "mo", 'output' => "|", 'tex' => null, 'ttype' => $LEFTBRACKET],
    ['input' => "=>|", 'tag' => "mo", 'output' => "|", 'tex' => null, 'ttype' => $RIGHTBRACKET],
    //{'input' =>"or", 'tag' =>"mo", 'output' =>"or", 'tex' =>null, 'ttype' =>$LEFTRIGHT},
    ['input' => "(=>", 'tag' => "mo", 'output' => "\u2329", 'tex' => "langle", 'ttype' => $LEFTBRACKET],
    ['input' => "=>)", 'tag' => "mo", 'output' => "\u232A", 'tex' => "rangle", 'ttype' => $RIGHTBRACKET],
    ['input' => "<<", 'tag' => "mo", 'output' => "\u2329", 'tex' => null, 'ttype' => $LEFTBRACKET],
    ['input' => ">>", 'tag' => "mo", 'output' => "\u232A", 'tex' => null, 'ttype' => $RIGHTBRACKET],
    ['input' => "{=>", 'tag' => "mo", 'output' => "{=>", 'tex' => null, 'ttype' => $LEFTBRACKET, 'invisible' => true],
    ['input' => "=>}", 'tag' => "mo", 'output' => "=>}", 'tex' => null, 'ttype' => $RIGHTBRACKET, 'invisible' => true],

    //miscellaneous symbols
    ['input' => "int", 'tag' => "mo", 'output' => "\u222B", 'tex' => null, 'ttype' => $CONST],
    ['input' => "dx", 'tag' => "mi", 'output' => "{=>d x=>}", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "dy", 'tag' => "mi", 'output' => "{=>d y=>}", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "dz", 'tag' => "mi", 'output' => "{=>d z=>}", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "dt", 'tag' => "mi", 'output' => "{=>d t=>}", 'tex' => null, 'ttype' => $DEFINITION],
    ['input' => "oint", 'tag' => "mo", 'output' => "\u222E", 'tex' => null, 'ttype' => $CONST],
    ['input' => "del", 'tag' => "mo", 'output' => "\u2202", 'tex' => "partial", 'ttype' => $CONST],
    ['input' => "grad", 'tag' => "mo", 'output' => "\u2207", 'tex' => "nabla", 'ttype' => $CONST],
    ['input' => "+-", 'tag' => "mo", 'output' => "\u00B1", 'tex' => "pm", 'ttype' => $CONST],
    ['input' => "-+", 'tag' => "mo", 'output' => "\u2213", 'tex' => "mp", 'ttype' => $CONST],
    ['input' => "O/", 'tag' => "mo", 'output' => "\u2205", 'tex' => "emptyset", 'ttype' => $CONST],
    ['input' => "oo", 'tag' => "mo", 'output' => "\u221E", 'tex' => "infty", 'ttype' => $CONST],
    ['input' => "aleph", 'tag' => "mo", 'output' => "\u2135", 'tex' => null, 'ttype' => $CONST],
    ['input' => "->->->", 'tag' => "mo", 'output' => "->->->", 'tex' => "ldots", 'ttype' => $CONST],
    ['input' => "=>->", 'tag' => "mo", 'output' => "\u2234", 'tex' => "therefore", 'ttype' => $CONST],
    ['input' => "=>'", 'tag' => "mo", 'output' => "\u2235", 'tex' => "because", 'ttype' => $CONST],
    ['input' => "/_", 'tag' => "mo", 'output' => "\u2220", 'tex' => "angle", 'ttype' => $CONST],
    ['input' => "/_\\", 'tag' => "mo", 'output' => "\u25B3", 'tex' => "triangle", 'ttype' => $CONST],
    ['input' => "'", 'tag' => "mo", 'output' => "\u2032", 'tex' => "prime", 'ttype' => $CONST],
    ['input' => "tilde", 'tag' => "mover", 'output' => "~", 'tex' => null, 'ttype' => $UNARY, 'acc' => true],
    ['input' => "\\ ", 'tag' => "mo", 'output' => "\u00A0", 'tex' => null, 'ttype' => $CONST],
    ['input' => "frown", 'tag' => "mo", 'output' => "\u2322", 'tex' => null, 'ttype' => $CONST],
    ['input' => "quad", 'tag' => "mo", 'output' => "\u00A0\u00A0", 'tex' => null, 'ttype' => $CONST],
    ['input' => "qquad", 'tag' => "mo", 'output' => "\u00A0\u00A0\u00A0\u00A0", 'tex' => null, 'ttype' => $CONST],
    ['input' => "thinspace", 'tag' => "mspace", 'output' => "0.17", 'tex' => null, 'ttype' => $CONST],
    ['input' => "mspace", 'tag' => "mspace", 'output' => "mspace", 'tex' => null, 'ttype' => $TEXT],
    ['input' => "cdots", 'tag' => "mo", 'output' => "\u22EF", 'tex' => null, 'ttype' => $CONST],
    ['input' => "vdots", 'tag' => "mo", 'output' => "\u22EE", 'tex' => null, 'ttype' => $CONST],
    ['input' => "ddots", 'tag' => "mo", 'output' => "\u22F1", 'tex' => null, 'ttype' => $CONST],
    ['input' => "diamond", 'tag' => "mo", 'output' => "\u22C4", 'tex' => null, 'ttype' => $CONST],
    ['input' => "square", 'tag' => "mo", 'output' => "\u25A1", 'tex' => null, 'ttype' => $CONST],
    ['input' => "|__", 'tag' => "mo", 'output' => "\u230A", 'tex' => "lfloor", 'ttype' => $CONST],
    ['input' => "__|", 'tag' => "mo", 'output' => "\u230B", 'tex' => "rfloor", 'ttype' => $CONST],
    ['input' => "|~", 'tag' => "mo", 'output' => "\u2308", 'tex' => "lceiling", 'ttype' => $CONST],
    ['input' => "~|", 'tag' => "mo", 'output' => "\u2309", 'tex' => "rceiling", 'ttype' => $CONST],
    ['input' => "CC", 'tag' => "mo", 'output' => "\u2102", 'tex' => null, 'ttype' => $CONST],
    ['input' => "NN", 'tag' => "mo", 'output' => "\u2115", 'tex' => null, 'ttype' => $CONST],
    ['input' => "QQ", 'tag' => "mo", 'output' => "\u211A", 'tex' => null, 'ttype' => $CONST],
    ['input' => "RR", 'tag' => "mo", 'output' => "\u211D", 'tex' => null, 'ttype' => $CONST],
    ['input' => "ZZ", 'tag' => "mo", 'output' => "\u2124", 'tex' => null, 'ttype' => $CONST],
    ['input' => "f", 'tag' => "mi", 'output' => "f", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "g", 'tag' => "mi", 'output' => "g", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "hbar", 'tag' => "mo", 'output' => "\u210F", 'tex' => null, 'ttype' => $CONST],

    //standard functions
    ['input' => "lim", 'tag' => "mo", 'output' => "lim", 'tex' => null, 'ttype' => $UNDEROVER],
    ['input' => "Lim", 'tag' => "mo", 'output' => "Lim", 'tex' => null, 'ttype' => $UNDEROVER],
    ['input' => "sin", 'tag' => "mo", 'output' => "sin", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "cos", 'tag' => "mo", 'output' => "cos", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "tan", 'tag' => "mo", 'output' => "tan", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "sinh", 'tag' => "mo", 'output' => "sinh", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "cosh", 'tag' => "mo", 'output' => "cosh", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "tanh", 'tag' => "mo", 'output' => "tanh", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "cot", 'tag' => "mo", 'output' => "cot", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "sec", 'tag' => "mo", 'output' => "sec", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "csc", 'tag' => "mo", 'output' => "csc", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "arcsin", 'tag' => "mo", 'output' => "arcsin", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "arccos", 'tag' => "mo", 'output' => "arccos", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "arctan", 'tag' => "mo", 'output' => "arctan", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "arcsec", 'tag' => "mo", 'output' => "arcsec", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "arccsc", 'tag' => "mo", 'output' => "arccsc", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "arccot", 'tag' => "mo", 'output' => "arccot", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "coth", 'tag' => "mo", 'output' => "coth", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "sech", 'tag' => "mo", 'output' => "sech", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "csch", 'tag' => "mo", 'output' => "csch", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "exp", 'tag' => "mo", 'output' => "exp", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "abs", 'tag' => "mo", 'output' => "abs", 'tex' => null, 'ttype' => $UNARY, 'rewriteleftright' => ["|", "|"]],
    ['input' => "norm", 'tag' => "mo", 'output' => "norm", 'tex' => null, 'ttype' => $UNARY, 'rewriteleftright' => ["\u2225", "\u2225"]],
    ['input' => "floor", 'tag' => "mo", 'output' => "floor", 'tex' => null, 'ttype' => $UNARY, 'rewriteleftright' => ["\u230A", "\u230B"]],
    ['input' => "ceil", 'tag' => "mo", 'output' => "ceil", 'tex' => null, 'ttype' => $UNARY, 'rewriteleftright' => ["\u2308", "\u2309"]],
    ['input' => "log", 'tag' => "mo", 'output' => "log", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "ln", 'tag' => "mo", 'output' => "ln", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "det", 'tag' => "mo", 'output' => "det", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "dim", 'tag' => "mo", 'output' => "dim", 'tex' => null, 'ttype' => $CONST],
    ['input' => "mod", 'tag' => "mo", 'output' => "mod", 'tex' => null, 'ttype' => $CONST],
    ['input' => "gcd", 'tag' => "mo", 'output' => "gcd", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "lcm", 'tag' => "mo", 'output' => "lcm", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "lub", 'tag' => "mo", 'output' => "lub", 'tex' => null, 'ttype' => $CONST],
    ['input' => "glb", 'tag' => "mo", 'output' => "glb", 'tex' => null, 'ttype' => $CONST],
    ['input' => "min", 'tag' => "mo", 'output' => "min", 'tex' => null, 'ttype' => $UNDEROVER],
    ['input' => "max", 'tag' => "mo", 'output' => "max", 'tex' => null, 'ttype' => $UNDEROVER],
    ['input' => "Sin", 'tag' => "mo", 'output' => "Sin", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Cos", 'tag' => "mo", 'output' => "Cos", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Tan", 'tag' => "mo", 'output' => "Tan", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Arcsin", 'tag' => "mo", 'output' => "Arcsin", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Arccos", 'tag' => "mo", 'output' => "Arccos", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Arctan", 'tag' => "mo", 'output' => "Arctan", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Sinh", 'tag' => "mo", 'output' => "Sinh", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Cosh", 'tag' => "mo", 'output' => "Cosh", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Tanh", 'tag' => "mo", 'output' => "Tanh", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Cot", 'tag' => "mo", 'output' => "Cot", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Sec", 'tag' => "mo", 'output' => "Sec", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Csc", 'tag' => "mo", 'output' => "Csc", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Log", 'tag' => "mo", 'output' => "Log", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Ln", 'tag' => "mo", 'output' => "Ln", 'tex' => null, 'ttype' => $UNARY, 'func' => true],
    ['input' => "Abs", 'tag' => "mo", 'output' => "abs", 'tex' => null, 'ttype' => $UNARY, 'notexcopy' => true, 'rewriteleftright' => ["|", "|"]],

    //arrows
    ['input' => "uarr", 'tag' => "mo", 'output' => "\u2191", 'tex' => "uparrow", 'ttype' => $CONST],
    ['input' => "darr", 'tag' => "mo", 'output' => "\u2193", 'tex' => "downarrow", 'ttype' => $CONST],
    ['input' => "rarr", 'tag' => "mo", 'output' => "\u2192", 'tex' => "rightarrow", 'ttype' => $CONST],
    ['input' => "->", 'tag' => "mo", 'output' => "\u2192", 'tex' => "to", 'ttype' => $CONST],
    ['input' => ">->", 'tag' => "mo", 'output' => "\u21A3", 'tex' => "rightarrowtail", 'ttype' => $CONST],
    ['input' => "->>", 'tag' => "mo", 'output' => "\u21A0", 'tex' => "twoheadrightarrow", 'ttype' => $CONST],
    ['input' => ">->>", 'tag' => "mo", 'output' => "\u2916", 'tex' => "twoheadrightarrowtail", 'ttype' => $CONST],
    ['input' => "|->", 'tag' => "mo", 'output' => "\u21A6", 'tex' => "mapsto", 'ttype' => $CONST],
    ['input' => "larr", 'tag' => "mo", 'output' => "\u2190", 'tex' => "leftarrow", 'ttype' => $CONST],
    ['input' => "harr", 'tag' => "mo", 'output' => "\u2194", 'tex' => "leftrightarrow", 'ttype' => $CONST],
    ['input' => "rArr", 'tag' => "mo", 'output' => "\u21D2", 'tex' => "Rightarrow", 'ttype' => $CONST],
    ['input' => "lArr", 'tag' => "mo", 'output' => "\u21D0", 'tex' => "Leftarrow", 'ttype' => $CONST],
    ['input' => "dArr", 'tag' => "mo", 'output' => "\u21D3", 'tex' => "Downarrow", 'ttype' => $CONST],
    ['input' => "hArr", 'tag' => "mo", 'output' => "\u21D4", 'tex' => "Leftrightarrow", 'ttype' => $CONST],
    ['input' => "rightleftharpoons", 'tag' => "mo", 'output' => "\u21CC", 'tex' => null, 'ttype' => $CONST],

    //commands with argument
    ['input' => "sqrt", 'tag' => "msqrt", 'output' => "sqrt", 'tex' => null, 'ttype' => $UNARY],
    ['input' => "root", 'tag' => "mroot", 'output' => "root", 'tex' => null, 'ttype' => $BINARY],
    ['input' => "frac", 'tag' => "mfrac", 'output' => "/", 'tex' => null, 'ttype' => $BINARY],
    ['input' => "/", 'tag' => "mfrac", 'output' => "/", 'tex' => null, 'ttype' => $INFIX],
    ['input' => "stackrel", 'tag' => "mover", 'output' => "stackrel", 'tex' => null, 'ttype' => $BINARY],
    ['input' => "overset", 'tag' => "mover", 'output' => "stackrel", 'tex' => null, 'ttype' => $BINARY],
    ['input' => "underset", 'tag' => "munder", 'output' => "stackrel", 'tex' => null, 'ttype' => $BINARY],
    ['input' => "_", 'tag' => "msub", 'output' => "_", 'tex' => null, 'ttype' => $INFIX],
    ['input' => "^", 'tag' => "msup", 'output' => "^", 'tex' => null, 'ttype' => $INFIX],
    ['input' => "hat", 'tag' => "mover", 'output' => "\u0302", 'tex' => null, 'ttype' => $UNARY, 'acc' => true],
    ['input' => "bar", 'tag' => "mover", 'output' => "\u00AF", 'tex' => "overline", 'ttype' => $UNARY, 'acc' => true],
    ['input' => "vec", 'tag' => "mover", 'output' => "\u2192", 'tex' => null, 'ttype' => $UNARY, 'acc' => true],
    ['input' => "dot", 'tag' => "mover", 'output' => "->", 'tex' => null, 'ttype' => $UNARY, 'acc' => true],
    ['input' => "ddot", 'tag' => "mover", 'output' => "->->", 'tex' => null, 'ttype' => $UNARY, 'acc' => true],
    ['input' => "overarc", 'tag' => "mover", 'output' => "\u23DC", 'tex' => "overparen", 'ttype' => $UNARY, 'acc' => true],
    ['input' => "ul", 'tag' => "munder", 'output' => "\u0332", 'tex' => "underline", 'ttype' => $UNARY, 'acc' => true],
    ['input' => "ubrace", 'tag' => "munder", 'output' => "\u23DF", 'tex' => "underbrace", 'ttype' => $UNARYUNDEROVER, 'acc' => true],
    ['input' => "obrace", 'tag' => "mover", 'output' => "\u23DE", 'tex' => "overbrace", 'ttype' => $UNARYUNDEROVER, 'acc' => true],
    ['input' => "text", 'tag' => "mtext", 'output' => "text", 'tex' => null, 'ttype' => $TEXT],
    ['input' => "mbox", 'tag' => "mtext", 'output' => "mbox", 'tex' => null, 'ttype' => $TEXT],
    ['input' => "color", 'tag' => "mstyle", 'output' => "", 'ttype' => $BINARY],
    ['input' => "id", 'tag' => "mrow", 'output' => "", 'ttype' => $BINARY],
    ['input' => "class", 'tag' => "mrow", 'output' => "", 'ttype' => $BINARY],
    ['input' => "cancel", 'tag' => "menclose", 'output' => "cancel", 'tex' => null, 'ttype' => $UNARY],
    $AMquote,
    ['input' => "bb", 'tag' => "", 'ttype' => $UNARY, 'tex' => "mathbf", 'output' => "", 'codes' => 'bold'],
    ['input' => "sf", 'tag' => "", 'ttype' => $UNARY, 'tex' => "mathsf", 'output' => "", 'codes' => 'sans-serif'],
    ['input' => "sfit", 'tag' => "", 'ttype' => $UNARY, 'output' => "", 'codes' => 'sans-serif-italic'],
    ['input' => "bbsf", 'tag' => "", 'ttype' => $UNARY, 'output' => "", 'codes' => 'bold-sans-serif'],
    ['input' => "bbb", 'tag' => "", 'ttype' => $UNARY, 'tex' => "mathbb", 'output' => "", 'codes' => 'double-struck'],
    ['input' => "cc", 'tag' => "", 'ttype' => $UNARY, 'tex' => "mathcal", 'output' => "", 'codes' => 'script'],
    ['input' => "bbcc", 'tag' => "", 'ttype' => $UNARY, 'output' => "", 'codes' => 'bold-script'],
    ['input' => "tt", 'tag' => "", 'ttype' => $UNARY, 'tex' => "mathtt", 'output' => "", 'codes' => 'monospace'],
    ['input' => "fr", 'tag' => "", 'ttype' => $UNARY, 'tex' => "mathfrak", 'output' => "", 'codes' => 'fraktur'],
    ['input' => "bbfr", 'tag' => "", 'ttype' => $UNARY, 'output' => "", 'codes' => 'bold-fraktur'],
    ['input' => "bbit", 'tag' => "", 'ttype' => $UNARY, 'output' => "", 'codes' => 'bold-italic'],
    ['input' => "bbsfit", 'tag' => "", 'ttype' => $UNARY, 'output' => "", 'codes' => 'sans-serif-bold-italic'],
    ['input' => "bold", 'tag' => "", 'ttype' => $UNARY, 'output' => ""],
    ['input' => "italic", 'tag' => "", 'ttype' => $UNARY, 'tex' => "mathit", 'output' => "", 'codes' => 'italic'],
];


//////   Parsing ASCII math expressions with the following grammar
// v ::= [A-Za-z] | greek letters | numbers | other constant symbols
// u ::= sqrt | text | bb | other unary symbols for font commands
// b ::= frac | root | stackrel         binary symbols
// l ::= ( | [ | { | (: | {:            left brackets
// r ::= ) | ] | } | :) | :}            right brackets
// S ::= v | lEr | uS | bSS             Simple expression
// $i ::= S_S | S^S | S_S^S | S          Intermediate expression
// E ::= IE | $i/$i                       Expression
// Each terminal symbol is translated into a corresponding mathml node->



// function compareNames($s1, $s2)  // outside classmust be static for usort
// {
//     if ($s1['input'] > $s2['input']) {
//         return 1;
//     } else {
//         return -1;
//     }
// }

class AMserver
{
    // <mstyle $this->mathcolor="blue" fontsize="1em" mathsize="1em" fontfamily="serif" mathvariant="serif" displaystyle="true"><mrow><mo>[</mo><mtable columnlines="none none"><mtr><mtd><mi>a</mi></mtd><mtd><mi>b</mi></mtd></mtr></mtable><mo>]</mo></mrow></mstyle>

    public $mathcolor = "blue";        // change it to "" (to inherit) or another color
    public $mathfontsize = "1em";      // change to e->g-> 1->2em for larger math
    public $mathfontfamily = "serif";  // change to "" to inherit (works in IE)

    public $AMmathml = "http://www->w3->org/1998/Math/MathML";

    public int $AMnestingDepth = 0;
    public int $AMpreviousSymbol = -1;  // eg: $INFIX
    public int $AMcurrentSymbol  = -1;

    public array $AMnames = []; //list of input symbols

    public $displaystyle = true;      // puts limits above and below large operators
    public $showasciiformulaonhover = false; // helps students learn ASCIIMath
    public $listseparator = ",";      // when decimalsign="," you can opt to use ";" as listseparator
    public $decimalsign = "->";        // if "," then when writing lists or matrices put
    public $addmathvariant = false;  // true to add mathvariant on font changes->

    function __construct()
    {
        $this->initSymbols();
    }


    function createMmlNode(string $t, AMNode|false $frag = false): AMNode
    {
        $node = new AMNode($t);
        if ($frag) {
            $node->appendChild($frag);
        }
        return $node;
    }


    /** replaces document->createTextNode() */
    function createTextNode(string $content): AMNode
    {
        $newNode = new AMNode('#text', $content);
        return $newNode;
    }

    /** replaces document->createDocumentFragment() */
    function createDocumentFragment(): AMNode
    {
        $newNode = new AMNode('');
        return $newNode;
    }

    function newcommand(string $oldstr, string $newstr)
    {
        global $AMsymbols, $DEFINITION;
        array_push($AMsymbols, ['input' => $oldstr, 'tag' => "mo", 'output' => $newstr, 'tex' => null, 'ttype' => $DEFINITION]);
        $this->refreshSymbols();
    }

    function newsymbol(array $symbolobj)
    {
        global $AMsymbols;
        $AMsymbols->push($symbolobj);
        $this->refreshSymbols();
    }



    function initSymbols()
    {
        global $AMsymbols;
        $symlen = count($AMsymbols);
        for ($i = 0; $i < $symlen; $i++) {
            if ($AMsymbols[$i]['tex']) {
                $AMsymbols->push(
                    [
                        'input' => $AMsymbols[$i]['tex'] ?? '',
                        'tag' => $AMsymbols[$i]['tag'],
                        'output' => $AMsymbols[$i]['output'],
                        'ttype' => $AMsymbols[$i]['ttype'],
                        'acc' => $AMsymbols[$i]['acc'] ?? false,
                        'codes' => $AMsymbols[$i]['codes'] ?? false,
                    ]
                );
            }
        }
        $this->refreshSymbols();
    }

    function refreshSymbols()
    {
        global $AMsymbols;
        usort($AMsymbols, function ($s1, $s2) {
            global $AMsymbols;
            for ($i = 0; $i < count($AMsymbols); $i++)
                $this->AMnames[$i] = $AMsymbols[$i]['input'];
        });
    }
    function define(string $oldstr, string $newstr)
    {
        global $AMsymbols, $DEFINITION;
        $AMsymbols->push(['input' => $oldstr, 'tag' => "mo", 'output' => $newstr, 'tex' => null, 'ttype' => $DEFINITION]);
        $this->refreshSymbols(); // $this may be a problem if many symbols are defined!
    }

    function AMremoveCharsAndBlanks(string $str, int $n)
    {
        //remove n characters and any following blanks
        if ($str[$n] == "\\" and $str[$n + 1] != "\\" and $str[$n + 1] != " ")
            $st = substr($str, $n, 1);
        else $st = substr($str, $n);
        for ($i = 0; $i < strlen($st) and $st[$i] <= 32; $i = $i + 1);
        return substr($st, $i);
    }

    function position(array $arr, string $str, int $n)
    {
        // return position >=n where str appears or would be inserted
        // assumes arr is sorted
        if ($n == 0) {
            $n = -1;
            $h = count($arr);
            while ($n + 1 < $h) {
                $m = ($n + $h) >> 1;
                if ($arr[$m] < $str) $n = $m;
                else $h = $m;
            }
            return $h;
        } else
            for ($i = $n; $i < count($arr) and $arr[$i] < $str; $i++);

        return $i; // $i=arr->length or arr[$i]>=str
    }

    function AMgetSymbol(string $str) /*: AMSymbol */
    {
        global $CONST;

        //return maximal initial substring of str that appears in names
        //return null if there is none
        $k = 0; //new pos
        $j = 0; //old pos
        $mk = -1; //match pos
        $match = "";
        $more = true;
        for ($i = 1; $i <= strlen($str) and $more; $i++) {
            $st = substr($str, 0, $i); //initial substring of length $i
            $j = $k;
            $k = $this->position($this->AMnames, $st, $j);
            if (k < count($this->AMnames) and substr($str, 0, strlen($this->AMnames[k])) == $this->AMnames[k]) {
                $match = $this->AMnames[$k];
                $mk = $k;
                $i = strlen($match);
            }
            $more = $k < strlen($this->AMnames) and str->slice(0, $this->AMnames[k]->length) >= $this->AMnames[k];
        }
        $this->AMpreviousSymbol = $this->AMcurrentSymbol;
        if ($match != "") {
            $this->AMcurrentSymbol = AMsymbols[$mk]['ttype'];
            return AMsymbols[$mk];
        }
        // if str[0] is a digit or - return maxsubstring of digits->digits
        $this->AMcurrentSymbol = $CONST;
        $k = 1;
        $useddecimal = false;
        $st = substr($str, 0, 1);
        $integ = true;
        while ("0" <= $st and $st <= "9" and $k <= strlen($str)) {
            $st = substr($str, $k, $k + 1);
            $k++;
        }
        if ($st == $this->decimalsign) {
            $st = substr($str, $k, $k + 1);
            if ($k > 1 and ($this->decimalsign != $this->listseparator or $st != " ") and $k < strlen($str)) {
                $k++;
                $useddecimal = true;
            }
            if ("0" <= $st and $st <= "9") {
                $integ = false;
                if (!$useddecimal) {
                    $k++;
                }
                while ("0" <= $st and $st <= "9" and $k <= strlen($str)) {
                    $st = substr($str, $k, $k + 1);
                    $k++;
                }
            }
        }
        if (($integ and $k > 1) or $k > 2) {
            $st = substr($str, 0, $k - 1);
            $tagst = "mn";
        } else {
            $k = 2;
            $st = substr($str, 0, 1); //take 1 character
            $tagst = (("A" > $st or $st > "Z") and ("a" > $st or $st > "z") ? "mo" : "mi");
        }
        if ($st == "-" and $str[charAt(1)] !== ' ' and $this->AMpreviousSymbol == $INFIX) {
            $this->AMcurrentSymbol = $INFIX;  //trick "/" into recognizing "-" on second parse
            return ['input' => $st, 'tag' => $tagst, 'output' => ($st == "-" ? "\u2212" : $st), 'ttype' => $UNARY, 'func' => true];
        }
        return ['input' => $st, 'tag' => $tagst, 'output' => ($st == "-" ? "\u2212" : $st), 'ttype' => $CONST];
    }

    function AMremoveBrackets(AMNode $node)
    {
        $st;
        // inserted lots of ! overrides because if hasChildNodes() is true then firstChild() and lastChild() must exist
        if (!$node->hasChildNodes()) {
            return;
        }
        if ($node->firstChild()->hasChildNodes() and ($node->nodeName == "mrow" or $node->nodeName == "M:MROW")) {
            if ($node->firstChild()->nextSibling() and $node->firstChild()->nextSibling()->nodeName == "mtable") {
                return;
            }
            $st = $node->firstChild()->firstChild()->nodeValue;
            if ($st == "(" or $st == "[" or $st == "{") $node->removeChild($node->firstChild());
        }
        if ($node->lastChild()->hasChildNodes() and ($node->nodeName == "mrow" or $node->nodeName == "M:MROW")) {
            $st = $node->lastChild()->firstChild()->nodeValue;
            if ($st == ")" or $st == "]" or $st == "}") {
                $node->removeChild($node->lastChild());
            }
        }
    }


    function AMparseSexpr(string $str) /*: [AMNode, string]*/
    { //parses str and returns [$node,tailstr]
        global $DEFINITION, $UNDEROVER, $CONST;

        $newFrag = $this->createDocumentFragment();
        $str = $this->AMremoveCharsAndBlanks($str, 0);
        $symbol = $this->AMgetSymbol($str);             //either a token or a bracket or empty

        //// AMparsSexpr isn't allowed to return null->  not sure wht $this can do->
        // if (symbol == null or symbol['ttype'] == $RIGHTBRACKET and $this->AMnestingDepth > 0) {
        //     return [null, str];
        // }

        if ($symbol['ttype'] == $DEFINITION) {
            $str = $symbol['output'] + $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
            $symbol = $this->AMgetSymbol(str);
        }
        switch ($symbol['ttype']) {
            case $UNDEROVER:
            case $CONST:
                $str = $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
                return [$this->createMmlNode(
                    $symbol['tag'],        //its a constant
                    $this->createTextNode($symbol['output'])
                ), str];
            case $LEFTBRACKET:   //read (expr+)
                $this->AMnestingDepth++;
                $str = $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
                if ($symbol['tag'] === 'mspace') {
                    $node = this . createMmlNode($symbol['tag']);
                    $node . setAttribute("width", $symbol['output'] + "em");
                    return [$node, $str];
                } else {
                    $result = $this->AMparseExpr(str, true);
                }
                $this->AMnestingDepth--;
                if ($symbol->invisible)
                    $node = $this->createMmlNode("mrow", $result[0]);
                else {
                    $node = $this->createMmlNode("mo", $this->createTextNode($symbol['output']));
                    $node = $this->createMmlNode("mrow", $node);
                    $node->appendChild($result[0]);
                }
                return [$node, $result[1]];
            case TEXT:
                if ($symbol != AMquote)
                    $str = $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
                if (str[0] == "{") $i = str->indexOf("}");
                else if (str[0] == "(") $i = str->indexOf(")");
                else if (str[0] == "[") $i = str->indexOf("]");
                else if ($symbol == AMquote) $i = str->slice(1)->indexOf("\"") + 1;
                else $i = 0;
                if ($i == -1) $i = strlen($str);
                $st = subst($str, 1, $i);
                if ($symbol['input'] === 'mspace') { // special case
                    $m = preg_match('^(-?[\d\.]+)\s*(em|mu)?$', $st);
                    if (!$m) {
                        $st = "0em";
                    } else if (!m[2] || $m[2] == "mu") {
                        $st = ($m[1] / 16) + "em";
                    }
                    $node = createMmlNode($symbol['tag']);
                    $node . setAttribute("width", $st);
                    $str = AMremoveCharsAndBlanks($str, i + 1);
                    return [$node, $str];
                }

                if ($st{
                    0} == " ") {
                    $node = $this->createMmlNode("mspace");
                    $node->setAttribute("width", "1ex");
                    $newFrag->appendChild($node);
                }
                $newFrag->appendChild(
                    $this->createMmlNode($symbol['tag'], $this->createTextNode($st))
                );
                if ($st{
                    $st->length - 1} == " ") {
                    $node = $this->createMmlNode("mspace");
                    $node->setAttribute("width", "1ex");
                    $newFrag->appendChild($node);
                }
                $str = $this->AMremoveCharsAndBlanks(str, $i + 1);
                return [$this->createMmlNode("mrow", newFrag), str];
            case $UNARYUNDEROVER:
            case $UNARY:
                $str = $this->AMremoveCharsAndBlanks(str, strlen($symbol['input']));
                $result = $this->AMparseSexpr(str);

                if ($result[0] == null) {
                    if ($symbol['tag'] == "mi" or $symbol['tag'] == "mo") {
                        return [$this->createMmlNode(
                            $symbol['tag'],
                            $this->createTextNode($symbol['output'])
                        ), str];
                    } else {
                        $result[0] = $this->createMmlNode("mi");
                    }
                }
                if ($symbol->func) { // functions hack
                    $st = $str[0];
                    if (
                        $st == "^" or $st == "_" or $st == "/" or $st == "|" or $st == $this->listseparator or
                        (strlen($symbol['input']) == 1 and preg_match('/\w/', $symbol['input']) and $st != "(")
                    ) {
                        return [$this->createMmlNode(
                            $symbol['tag'],
                            $this->createTextNode($symbol['output'])
                        ), $str];
                    } else {
                        $node = $this->createMmlNode(
                            "mrow",
                            $this->createMmlNode($symbol['tag'], $this->createTextNode($symbol['output']))
                        );
                        $node->appendChild($result[0]);
                        return [$node, $result[1]];
                    }
                }
                $this->AMremoveBrackets($result[0]);
                if ($symbol['input'] == "sqrt") {           // sqrt
                    return [$this->createMmlNode($symbol['tag'], $result[0]), $result[1]];
                } else if (exists($symbol['rewriteleftright'])) {    // abs, floor, ceil
                    $node = $this->createMmlNode("mrow", $this->createMmlNode("mo", $this->createTextNode($symbol->rewriteleftright[0])));
                    $node->appendChild($result[0]);
                    $node->appendChild($this->createMmlNode("mo", $this->createTextNode($symbol->rewriteleftright[1])));
                    return [$node, $result[1]];
                } else if ($symbol['input'] == "cancel") {   // cancel
                    $node = $this->createMmlNode($symbol['tag'], $result[0]);
                    $node->setAttribute("notation", "updiagonalstrike");
                    return [$node, $result[1]];
                } else if (exists($symbol['acc']) and $symbol['acc']) {   // accent
                    $node = $this->createMmlNode($symbol['tag'], $result[0]);
                    if ($symbol['tag'] == 'mover' and $symbol['ttype'] == $UNARY) {
                        $node->setAttribute("accent", "true");
                    } else if ($symbol['tag'] == 'munder' and $symbol['ttype'] == $UNARY) {
                        $node->setAttribute("accentunder", "true");
                    }
                    $accnode = $this->createMmlNode("mo", $this->createTextNode($symbol['output']));
                    if (
                        $symbol['input'] == "vec" and (
                            ($result[0]->nodeName == "mrow" and $result[0]->childNodes->length == 1
                                and $result[0]->firstChild()->firstChild()->nodeValue !== null
                                and $result[0]->firstChild()->firstChild()->nodeValue->length == 1) or
                            ($result[0]->firstChild() and $result[0]->firstChild()->nodeValue !== null
                                and $result[0]->firstChild()->nodeValue->length == 1))
                    ) {
                        // special case of single character base for vector accent,
                        // where stretchy can make it look bad
                        $accnode->setAttribute("stretchy", false);
                    } else {
                        $accnode->setAttribute("stretchy", true);
                    }
                    $node->appendChild($accnode);
                    return [$node, $result[1]];
                } else if ($symbol['input'] == "bold") {
                    $result[0]->style['fontWeight'] = "bold";
                    return [$result[0], $result[1]];
                } else if ($symbol['input'] == "italic") {
                    $result[0]->style['fontStyle'] = "italic";
                    return [$result[0], $result[1]];
                } else {                        // font change command
                    if (exists($symbol['codes'])) {
                        $this->AMmapChars($result[0], $symbol['codes'], $symbol['input']);
                    }
                    return [$result[0], $result[1]];
                }
            case $BINARY:
                $str = $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
                $result = $this->AMparseSexpr(str);
                if ($result[0] == null) return [$this->createMmlNode(
                    "mo",
                    $this->createTextNode($symbol['input'])
                ), str];
                $this->AMremoveBrackets($result[0]);
                $result2 = $this->AMparseSexpr($result[1]);
                if (result2[0] == null) return [$this->createMmlNode(
                    "mo",
                    $this->createTextNode($symbol['input'])
                ), str];
                $this->AMremoveBrackets(result2[0]);
                if (in_array($symbol['input'], ['color', 'class', 'id'])) {

                    // Get the second argument
                    if ($str[0] == "{")      $i = strpos($str, '}');
                    else if ($str[0] == "(") $i = strpos($str, ")");
                    else if ($str[0] == "[") $i = strpos($str, "]");
                    $st = substr($str, $i);

                    // Make a mathml $node
                    $node = $this->createMmlNode($symbol['tag'], result2[0]);

                    // Set the correct attribute
                    if ($symbol['input'] === "color") $node->setAttribute("mathcolor", st);
                    else if ($symbol['input'] === "class") $node->setAttribute("class", st);
                    else if ($symbol['input'] === "id") $node->setAttribute("id", st);
                    return [$node, result2[1]];
                }
                if ($symbol['input'] == "root" or $symbol['output'] == "stackrel")
                    newFrag->appendChild(result2[0]);
                newFrag->appendChild($result[0]);
                if ($symbol['input'] == "frac") newFrag->appendChild(result2[0]);
                return [$this->createMmlNode($symbol['tag'], newFrag), result2[1]];
            case $INFIX:
                $str = $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
                return [$this->createMmlNode("mo", $this->createTextNode($symbol['output'])), str];
            case $SPACE:
                $str = $this->AMremoveCharsAndBlanks(str, strlen($symbol['input']));
                $node = $this->createMmlNode("mspace");
                $node->setAttribute("width", "1ex");
                $newFrag->appendChild($node);
                $newFrag->appendChild(
                    $this->createMmlNode($symbol['tag'], $this->createTextNode($symbol['output']))
                );
                $node = $this->createMmlNode("mspace");
                $node->setAttribute("width", "1ex");
                $newFrag->appendChild($node);
                return [$this->createMmlNode("mrow", newFrag), str];
            case $LEFTRIGHT:
                //    if (rightvert) return [null,str]; else rightvert = true;
                $this->AMnestingDepth++;
                $str = $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
                $result = $this->AMparseExpr($str, false);
                $this->AMnestingDepth--;
                $st = "";
                if ($result[0]->lastChild() != null)
                    $st = $result[0]->lastChild()->firstChild()->nodeValue;
                if ($st == "|" and $str[0] !== $this->listseparator) { // its an absolute value subterm
                    $node = $this->createMmlNode("mo", $this->createTextNode($symbol['output']));
                    $node = $this->createMmlNode("mrow", $node);
                    $node->appendChild($result[0]);
                    return [$node, $result[1]];
                } else { // the "|" is a \mid so use unicode 2223 (divides) for spacing
                    $node = $this->createMmlNode("mo", $this->createTextNode("\u2223"));
                    $node = $this->createMmlNode("mrow", $node);
                    return [$node, str];
                }
            default:
                //alert("default");
                $str = $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
                return [$this->createMmlNode(
                    $symbol['tag'],        //its a constant
                    $this->createTextNode($symbol['output'])
                ), str];
        }
    }

    // walks a node, and maps characters according to codemap
    function AMmapChars(AMNode $node, string $variant, string $inputsym)
    {
        $tag = '';
        $codemap = codemaps[variant];
        if (!codemap[2] and inputsym->substring(0, 2) == 'bb') {
            // bold but variant doesn't have symbol; use codepoint from bb codemap instead
            codemap[2] = codemaps['bold'][2];
        }
        $remap = exists($codemap[5]) ? $codemap[5] : [];
        if ($node->tagName) {
            $tag = strtoupper($node->tagName);
        }
        if (tag == "MI" or tag == "MO" or tag == "MN" or tag == "MTEXT") {
            if ($this->addmathvariant) {
                $node->setAttribute("mathvariant", variant);
            }
            $st = $node->firstChild()->nodeValue->toString();
            $newst = "";
            for ($j = 0; $j < strlen($st); $j++) {
                $didmap = false;
                $charcode = st->charCodeAt(j);
                for ($k = 0; $k < 5; $k++) {
                    if (!$codemap[k]) {
                        continue;
                    }
                    $map = $codemapranges[k][2]; // or {};
                    if ($map[charcode]) {
                        $newst .= String->fromCodePoint(map[charcode] - codemapranges[k][0] + codemap[k]);
                        $didmap = true;
                        break;
                    } else if ($charcode >= codemapranges[k][0] and charcode <= codemapranges[k][1]) {
                        $newst .= String->fromCodePoint(remap[charcode] or charcode - codemapranges[k][0] + codemap[k]);
                        $didmap = true;
                        break;
                    }
                }
                if (!$didmap) {
                    $newst .= $st{
                        $j};
                }
            }
            $node->replaceChild($this->createTextNode(newst), $node->firstChild());
        } else {
            for ($i = 0; $i < $node->childNodes->length; $i++) {
                $this->AMmapChars($node->childNodes[$i], variant, inputsym);
            }
        }
    }

    function AMparseIexpr(string $str) /*: [AMNode, string]*/
    {
        $str = $this->AMremoveCharsAndBlanks($str, 0);
        $sym1 = $this->AMgetSymbol($str);
        $result = $this->AMparseSexpr($str);
        $node = $result[0];
        $str = $result[1];
        $symbol = $this->AMgetSymbol($str);
        if ($symbol['ttype'] == $INFIX and $symbol['input'] != "/") {
            $str = $this->AMremoveCharsAndBlanks($str, strlen($symbol['input']));
            //    if ($symbol['input'] == "/") $result = AMparseIexpr($str); else ->->->
            $result = $this->AMparseSexpr($str);
            if ($result[0] == null) // show box in place of missing argument
                $result[0] = $this->createMmlNode("mo", $this->createTextNode("\u25A1"));
            else $this->AMremoveBrackets($result[0]);
            $str = $result[1];
            //    if ($symbol['input'] == "/") AMremoveBrackets($node);
            $underover = ($sym1['ttype'] == $UNDEROVER or $sym1['ttype'] == $UNARYUNDEROVER);
            if ($symbol['input'] == "_") {
                $sym2 = $this->AMgetSymbol($str);
                if ($sym2['input'] == "^") {
                    $str = $this->AMremoveCharsAndBlanks($str, $sym2['input']->length);
                    $res2 = $this->AMparseSexpr($str);
                    $this->AMremoveBrackets(res2[0]);
                    $str = res2[1];
                    $node = $this->createMmlNode(($underover ? "munderover" : "msubsup"), $node);
                    $node->appendChild($result[0]);
                    $node->appendChild(res2[0]);
                    $node = $this->createMmlNode("mrow", $node); // so sum does not stretch
                } else {
                    $node = $this->createMmlNode(($underover ? "munder" : "msub"), $node);
                    $node->appendChild($result[0]);
                }
            } else if ($symbol['input'] == "^" and $underover) {
                $node = $this->createMmlNode("mover", $node);
                $node->appendChild($result[0]);
            } else {
                $node = $this->createMmlNode($symbol['tag'], $node);
                $node->appendChild($result[0]);
            }
            if ($sym1->func) {
                $sym2 = $this->AMgetSymbol($str);
                if (
                    $sym2['ttype'] != $INFIX and $sym2['ttype'] != $RIGHTBRACKET and
                    ($sym1['input']->length > 1 or $sym2['ttype'] == $LEFTBRACKET)
                ) {
                    $result = $this->AMparseIexpr($str);
                    $node = $this->createMmlNode("mrow", $node);
                    $node->appendChild($result[0]);
                    $str = $result[1];
                }
            }
        }
        return [$node, $str];
    }

    function AMparseExpr(string $str, bool $rightbracket = false) /*: [AMNode, string]*/
    {
        // $symbol: AMSymbol, $node: AMNode, result, $i
        $newFrag = $this->createDocumentFragment();
        do {
            $str = $this->AMremoveCharsAndBlanks($str, 0);
            $result = $this->AMparseIexpr($str);
            $node = result[0];
            $str = result[1];
            $symbol = $this->AMgetSymbol($str);
            if (symbol['ttype'] == $INFIX and symbol['input'] == "/") {
                $str = $this->AMremoveCharsAndBlanks($str, symbol['input']->length);
                $result = $this->AMparseIexpr($str);
                if ($result[0] == null) // show box in place of missing argument
                    $result[0] = $this->createMmlNode("mo", $this->createTextNode("\u25A1"));
                else $this->AMremoveBrackets(result[0]);
                $str = $result[1];
                $this->AMremoveBrackets($node);
                $node = $this->createMmlNode(symbol['tag'], $node);
                $node->appendChild(result[0]);
                $newFrag->appendChild($node);
                $symbol = $this->AMgetSymbol($str);
            } else if ($node != undefined) newFrag->appendChild($node);
        } while ((symbol['ttype'] != $RIGHTBRACKET and
            (symbol['ttype'] != $LEFTRIGHT or rightbracket)
            or $this->AMnestingDepth == 0) and symbol != null and symbol['output'] != "");
        if (symbol['ttype'] == $RIGHTBRACKET or symbol['ttype'] == $LEFTRIGHT) {
            //    if ($this->AMnestingDepth > 0) $this->AMnestingDepth--;
            $len = newFrag->childNodes->length;
            if (
                len > 0 and newFrag->childNodes[len - 1]->nodeName == "mrow"
                and newFrag->childNodes[len - 1]->lastChild()
                and newFrag->childNodes[len - 1]->lastChild()->firstChild()
            ) { //matrix
                //removed to allow row vectors: //and len>1 and
                //newFrag->childNodes[len-2]->nodeName == "mo" and
                //newFrag->childNodes[len-2]->firstChild()->nodeValue == ","
                $right = newFrag->childNodes[len - 1]->lastChild()->firstChild()->nodeValue;
                if (right == ")" or right == "]") {
                    $left = newFrag->childNodes[len - 1]->firstChild()->firstChild()->nodeValue;
                    if (
                        left == "(" and right == ")" and symbol['output'] != "}" or
                        left == "[" and right == "]"
                    ) {
                        $pos = []; // positions of commas
                        $matrix = true;
                        $m = count(newFrag->childNodes);
                        for ($i = 0; matrix and $i < m; $i = $i + 2) {
                            pos[$i] = [];
                            $node = newFrag->childNodes[$i];
                            if ($matrix) $matrix = $node->nodeName == "mrow" and
                                ($i == m - 1 or $node->nextSibling()->nodeName == "mo" and
                                    $node->nextSibling()->firstChild()->nodeValue == $this->listseparator) and
                                $node->firstChild()->firstChild() !== null and
                                $node->firstChild()->firstChild()->nodeValue == left and
                                $node->lastChild()->firstChild() !== null and
                                $node->lastChild()->firstChild()->nodeValue == right;
                            if ($matrix)
                                for ($j = 0; $j < $node->childNodes->length; $j++)
                                    if ($node->childNodes[j]->firstChild()->nodeValue == $this->listseparator)
                                        pos[$i][pos[$i]->length] = $j;
                            if ($matrix and $i > 1) $matrix = strlen(pos[$i]) == pos[$i - 2]->length;
                        }
                        $matrix = $matrix and (strlen($pos) > 1 or strlen($pos[0]) > 0);
                        $columnlines = [];
                        if ($matrix) {
                            // $row: AMNode, $frag: AMNode, n, k,
                            $table = $this->createDocumentFragment();
                            for ($i = 0; $i < $m; $i = $i + 2) {
                                $row = $this->createDocumentFragment();
                                $frag = $this->createDocumentFragment();
                                $node = newFrag->firstChild(); // <mrow>(-,-,->->->,-,-)</mrow>
                                $n = $node->childNodes->length;
                                $k = 0;
                                $node->removeChild($node->firstChild()); //remove (
                                for ($j = 1; $j < $n - 1; $j++) {
                                    if (exists(pos[$i][$k]) and $j == pos[$i][$k]) {
                                        $node->removeChild($node->firstChild()); //remove ,
                                        if (
                                            $node->firstChild()->nodeName == "mrow" and $node->firstChild()->childNodes->length == 1 and
                                            $node->firstChild()->firstChild()->firstChild()->nodeValue == "\u2223"
                                        ) {
                                            //is columnline marker - skip it
                                            if ($i == 0) {
                                                columnlines->push("solid");
                                            }
                                            $node->removeChild($node->firstChild()); //remove mrow
                                            $node->removeChild($node->firstChild()); //remove ,
                                            $j .= 2;
                                            $k++;
                                        } else if ($i == 0) {
                                            columnlines->push("none");
                                        }
                                        $row->appendChild($this->createMmlNode("mtd", $frag));
                                        $k++;
                                    } else $frag->appendChild($node->firstChild());
                                }
                                row->appendChild($this->createMmlNode("mtd", $frag));
                                if ($i == 0) {
                                    columnlines->push("none");
                                }
                                if (newFrag->childNodes->length > 2) {
                                    newFrag->removeChild(newFrag->firstChild()); //remove <mrow>)</mrow>
                                    newFrag->removeChild(newFrag->firstChild()); //remove <mo>,</mo>
                                }
                                table->appendChild($this->createMmlNode("mtr", row));
                            }
                            $node = $this->createMmlNode("mtable", table);
                            $node->setAttribute("columnlines", columnlines->join(" "));
                            if (symbol->invisible) $node->setAttribute("columnalign", "left");
                            newFrag->replaceChild($node, newFrag->firstChild());
                        }
                    }
                }
            }
            $str = $this->AMremoveCharsAndBlanks($str, strlen(symbol['input']));
            if (!$symbol->invisible) {
                $node = $this->createMmlNode("mo", $this->createTextNode(symbol['output']));
                $newFrag->appendChild($node);
            }
        }
        return [newFrag, $str];
    }

    function parseMath(string $str): string
    {
        $this->AMnestingDepth = 0;
        //some basic cleanup for dealing with stuff editors like TinyMCE adds
        // $str = str_replace( '&nbsp;', "",$str);
        // $str = str_replace('&gt;', ">", $str);
        // $str = str_replace('&lt;', "<",$str);
        $frag = $this->AMparseExpr(str_replace('^', "", $str));
        $node = $this->createMmlNode("mstyle", $frag);
        if ($this->mathcolor != "") $node->setAttribute("mathcolor", $this->mathcolor);
        if ($this->mathfontsize != "") {
            $node->setAttribute("fontsize", $this->mathfontsize);
            $node->setAttribute("mathsize", $this->mathfontsize);
        }
        if ($this->mathfontfamily != "") {
            $node->setAttribute("fontfamily", $this->mathfontfamily);
            $node->setAttribute("mathvariant", $this->mathfontfamily);
        }

        if ($this->displaystyle)
            $node->setAttribute("displaystyle", "true");
        $node = $this->createMmlNode("math", $node);
        if ($this->showasciiformulaonhover) {                     //fixed by djhsu so newline
            $node = $this->createMmlNode('div', $node);
            $node->setAttribute("title", preg_replace('\s+', " ", $str)); //does not show in Gecko
        }
        return $node->flatten();
    }
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




$html = "";
$html .= "<!DOCTYPE html><html>";
$html .= "<head></head>";
$html .= "<body>";

$html .= "<br>alive<br>";
$html .= testAMNode();

$html .= "</body>";
$html .= "</body>";

echo $html;
