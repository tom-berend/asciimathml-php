asciimathml-php
===========

A port of ASCIIMathML.js into PHP (and TypeScript) that converts AsciiMath equations into HTML strings, suitable for server-side. 

ASCIIMathML compiles equations into MathML.  It is elegant, intuitive, and much easier to learn than LaTeX.

The original [ASCIIMathML](https://github.com/asciimath/asciimathml) used DOM nodes to process the hierarchical structure. This is convenient, since the output was a MathML node-tree, and ASCIIMathML provides MathJax-like rendering daemons.  It's a terrific package.


- Updated for the April 2026 version of ASCIIMathML, which added bold() and expands the special characters.
- Updated for the May 2026 version which adds mspace(), thinspace, and other spacing options.
- Updated for the June 2026 version which improved handling of matrices.


Includes a CSS hack for 'cancel' that also works for Chrome.  Some versions of Chrome don't offer a Math font by default. Include this link in your header:
```
<link href="https://fonts.googleapis.com/css2?family=STIX Two Math" rel="stylesheet">
```


## PHP Usage
```
require_once ('asciimath.php');
$am = new AMserver();

$html += $am->parseMath('a^b +c');
echo $html;


```

## TS Usage
```
import { AMserver } from './asciimath.js';
let am = new AMserver();
html += am.parseMath('a^b +c');

let d = document.getElementById('test');
d.innerHTML =  html;


```







