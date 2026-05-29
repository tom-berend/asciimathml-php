asciimathml-ts
===========

A port of ASCIIMathML.js that converts AsciiMath equations into HTML strings, suitable for server-side.

ASCIIMathML compiles equations into MathML.  The original [ASCIIMathML](https://github.com/asciimath/asciimathml) used DOM nodes to process the hierarchical structure. This is convenient, since the output was a MathML node-tree, and ASCIIMathML provides MathJax-like rendering daemons.  It's a terrific package.

But it can only run in the browser.  I needed a server version that compiled equations into HTML strings. So I added a simple


- Updates for the April 2026 version of ASCIIMathML, which added bold() and expands the special characters.
- Updated for the May 2026 version which adds mspace(), thinspace, and other spacing options.

Includes a CSS hack for 'cancel' that also works for Chrome.


## Usage
```
import { AMserver } from './asciimath.js';
let am = new AMserver()
html += am.parseMath('a^b +c')

let d = document.getElementById('test')
d.innerHTML =  html


```







