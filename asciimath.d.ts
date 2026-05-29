export declare class AMNode {
    nodeName: string;
    nodeValue: string;
    parent: AMNode | null;
    childNodes: AMNode[];
    children: AMNode[];
    attributes: {
        [key: string]: any;
    };
    style: string;
    unique: symbol;
    constructor(t: string, content?: string);
    appendChild(frag: AMNode): AMNode;
    setAttribute(key: string, value: any): AMNode;
    get firstChild(): AMNode;
    get lastChild(): AMNode;
    get tagName(): string;
    get nextSibling(): AMNode | null;
    hasChildNodes(): boolean;
    replaceChild(newChild: AMNode, oldChild: AMNode): AMNode;
    removeChild(node: AMNode): AMNode;
    /** turn a tree of AMNodes into an HTML string */
    flatten(): string;
}
type AMSymbol = {
    input: string;
    tag: string;
    output: string;
    tex?: string | null;
    ttype: number;
    invisible?: boolean;
    func?: boolean;
    acc?: boolean;
    rewriteleftright?: string[];
    notexcopy?: boolean;
    atname?: "mathvariant";
    atval?: "bold" | "sans-serif" | "double-struck" | "script" | "fraktur" | "monospace";
    codes?: string | boolean;
};
export declare class AMserver {
    mathcolor: string;
    mathfontsize: string;
    mathfontfamily: string;
    AMmathml: string;
    AMnestingDepth: number;
    AMpreviousSymbol: number;
    AMcurrentSymbol: number;
    AMnames: string[];
    displaystyle: boolean;
    showasciiformulaonhover: boolean;
    listseparator: string;
    decimalsign: string;
    addmathvariant: boolean;
    cancelColor: string;
    constructor();
    cancelStyle(color: string): string;
    createMmlNode(t: string, frag?: AMNode): AMNode;
    /** replaces document.createTextNode() */
    createTextNode(content: string): AMNode;
    /** replaces document.createDocumentFragment() */
    createDocumentFragment(): AMNode;
    newcommand(oldstr: string, newstr: string): void;
    newsymbol(symbolobj: AMSymbol): void;
    compareNames(s1: AMSymbol, s2: AMSymbol): 1 | -1;
    initSymbols(): void;
    refreshSymbols(): void;
    define(oldstr: string, newstr: string): void;
    AMremoveCharsAndBlanks(str: string, n: number): string;
    position(arr: string[], str: string, n: number): number;
    AMgetSymbol(str: string): AMSymbol;
    AMremoveBrackets(node: AMNode): void;
    AMparseSexpr(str: string): [AMNode, string];
    AMmapChars(node: AMNode, variant: string, inputsym: string): void;
    AMparseIexpr(str: string): [AMNode, string];
    AMparseExpr(str: string, rightbracket?: boolean): [AMNode, string];
    parseMath(str: string): string;
}
export {};
