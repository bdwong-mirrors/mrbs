/*
 * Cross-Browser Library of JavaScript Functions
 *
 * Inspired by the excellent xbDOM.js (and using it for debugging)
 *
 * $Id$
 */

if (!window.doAlert) var doAlert = false;
if (!window.xbDump) var xbDump = function(string, tag) {return;} // If indeed not defined, define as a dummy routine.

if (doAlert) alert("Started xbLib.js v4");
xbDump("Started xbLib.js v4");

/*****************************************************************************\
*		Part 1: Generic Cross-Browser Library routines		      *
\*****************************************************************************/

// Browser-independant background color management
function xblGetNodeBgColor(node)
    {
    if (!node) return null;
//    xbDump("node.bgColor = " + (node.bgColor ? node.bgColor : "<undefined>"));
    if (node.style)
	{
//        xbDump("node.style.getPropertyValue(\"background-color\") = " + (node.style.getPropertyValue ? ("\""+node.style.getPropertyValue("background-color")+"\"") : "<undefined>"));
//        xbDump("node.style.getAttribute(\"backgroundColor\") = " + (node.style.getAttribute ? ("\""+node.style.getAttribute("backgroundColor")+"\"") : "<undefined>"));
//        xbDump("node.style.backgroundColor = " + (node.style.backgroundColor ? node.style.backgroundColor : "<undefined>"));
	if (node.style.getPropertyValue)	// If DOM level 2 supported, the NS 6 way
            {
            return node.style.getPropertyValue("background-color");
            }
	if (node.style.getAttribute)		// If DOM level 2 supported, the IE 6 way
            {
            return node.style.getAttribute("backgroundColor");
            }
        return node.style.backgroundColor;	// Else DOM support is very limited.
	}
    // Else this browser is not DOM compliant. Try getting a classic attribute.
    return node.bgColor;
    }
function xblSetNodeBgColor(node, color)
    {
    if (!node) return;
    if (node.style)
	{
	if (node.style.setProperty)		// If DOM level 2 supported, the NS 6 way
            {
            node.style.setProperty("background-color", color, "");
	    return;
            }
	if (node.style.setAttribute)		// If DOM level 2 supported, the IE 6 way
            {
            node.style.setAttribute("backgroundColor", color);
	    return;
            }
	// Else this browser has very limited DOM support. Try setting the attribute directly.
        node.style.backgroundColor = color;	// Works on Opera 6
	return;
	}
    // Else this browser is not DOM compliant. Try setting a classic attribute.
    node.bgColor = color;
    }

// Browser-independant node tree traversal
function xblChildNodes(node)
    {
    if (!node) return null;
    if (node.childNodes) return node.childNodes;	// DOM-compliant browsers
    if (node.children) return node.children;		// Pre-DOM browsers like Opera 6
    return null;
    }
function xblFirstSibling(node)
    {
    if (!node) return null;
    var siblings = xblChildNodes(node.parentNode);
    if (!siblings) return null;
    return siblings[0];
    }
function xblLastSibling(node)
    {
    if (!node) return null;
    var siblings = xblChildNodes(node.parentNode);
    if (!siblings) return null;
    return siblings[siblings.length - 1];
    }

var xbGetElementById;
if (document.getElementById) // DOM level 2
    xblGetElementById = function(id) { return document.getElementById(id); };
else if (document.layers)	  // NS 4
    xblGetElementById = function(id) { return document.layers[id]; };
else if (document.all)		  // IE 4
    xblGetElementById = function(id) { return document.all[id]; };
else
    xblGetElementById = function(id) { return null; };

// Browser-independant style sheet rules scan.
function xbForEachCssRule(callback, ref)
    {
    if (document.styleSheets) for (var i=0; i<document.styleSheets.length; i++) 
        {
        var sheet = document.styleSheets.item(i);
        // xbDump("Style sheet " + i, "h3"); 
        // xbDumpProps(sheet);
        // If the browser is kind enough for having already split the CSS rules as specified by DOM... (NS6)
        if (sheet.cssRules) for (var j=0; j<sheet.cssRules.length; j++)
            {
            var rule = sheet.cssRules.item(j);
            // xbDump("Rule " + j, "h4");
            // xbDumpProps(rule);
            var result = callback(rule, ref);
            if (result) return result;
            }
        else if (sheet.cssText) // Else pass it the whole set at once (IE6)
            {
            // TO DO EVENTUALLY: Split the list into individual rules!
            var result = callback(sheet, ref);
            if (result) return result;
            }
        }
    return false;
    }

/*---------------------------------------------------------------------------*\
*									      *
|   Function:	    ForEachChild					      |
|									      |
|   Description:    Apply a method to each child node of an object.	      |
|									      |
|   Parameters:     Object obj          The object. Typically a DOM node.     |
|                   Function callback   Callback function.                    |
|                   Object ref          Reference object.                     |
|									      |
|   Returns:        The first non-null result reported by the callback.       |
|									      |
|   Support:        NS4           No. Returns null.                           |
|                   IE5+, NS6+    Yes.                                        |
|                   Opera 6       Yes.                                        |
|									      |
|   Notes:          The callback prototype is:                                |
|                   int callback(obj, ref);                                   |
|                   If the callback returns !null, the loop stops.            |
|									      |
|   History:                                                                  |
|									      |
|    2002/03/04 JFL Initial implementation.                                   |
|    2002/03/25 JFL Simplified the implementation.                            |
*									      *
\*---------------------------------------------------------------------------*/

function ForEachChild(obj, callback, ref)
  {
  if (!obj) return null;
  
  var children = xblChildNodes(obj);
  if (!children) return null;
    
  var nChildren = children.length;
  for (var i=0; i<nChildren; i++) 
    {
    var result = callback(children[i], ref);
    if (result) return result;
    }
  return null;
  }

/*---------------------------------------------------------------------------*\
*									      *
|   Function:       ForEachDescendant                                         |
|									      |
|   Description:    Apply a method to each descendant node of an object.      |
|									      |
|   Parameters:     Object obj          The object. Typically a DOM node.     |
|                   Function callback   Callback function.                    |
|                   Object ref          Reference object.                     |
|									      |
|   Returns:        The first non-null result reported by the callback.       |
|									      |
|   Support:        NS4           No. Returns null.                           |
|                   IE5+, NS6+    Yes.                                        |
|                   Opera 6       Yes.                                        |
|									      |
|   Notes:          The callback prototype is:                                |
|                   int callback(obj, ref);                                   |
|                   If the callback returns !null, the loop stops.            |
|									      |
|   History:                                                                  |
|									      |
|    2002/10/29 JFL Initial implementation.				      |
*									      *
\*---------------------------------------------------------------------------*/

function ForEachDescendantCB(obj, ref)
  {
  var result = ref.cb(obj, ref.rf);
  if (result) return result;
  return ForEachChild(obj, ForEachDescendantCB, ref);
  }

function ForEachDescendant(obj, callback, ref)
  {
  if (!obj) return null;
  var ref1 = {cb:callback, rf:ref};
  var result = ForEachChild(obj, ForEachDescendantCB, ref1);
  delete ref1;
  return result;
  }

//----------------------------------------------------//

function GetNodeType(node)
  {
  if (!node) return "null";
  if (node.tagName) return node.tagName;	// DOM-compliant tag name.
  if (node.nodeName) return node.nodeName;	// Implicit nodes, such as #text.
  if (window.xbDebugPersistToString) return xbDebugPersistToString(node);
  return "Unknown";
  }

// Debug routine for getting a canonic DOM pathname to a node.
function GetNodePathname(node)
  {
  var name = null;
  for (lastnode = null; node && (node != lastnode); lastnode = node, node = node.parentNode)
    {
    var nodename = GetNodeType(node);
    var siblings = xblChildNodes(node.parentNode);
    if (siblings)
      {
      var nSiblings = siblings.length;
      var nAkin = 0;
      var iAkin = -1;
      for (var i=0; i<nSiblings ; i++) 
        {
        var siblingname = GetNodeType(siblings[i]);
        if (siblingname && (siblingname == nodename))
          {
          if (siblings[i] == node) iAkin = nAkin;
          nAkin += 1;
          }
        }
      if ((nAkin > 1) && (iAkin >= 0)) nodename = nodename + "[" + iAkin + "]";
      }
    if (name)
      name = nodename + "." + name;
    else
      name = nodename;
    }
  return name;
  }

function GetAncestor(node, type)
  {
  for (node=node.parentNode; node; node=node.parentNode)
    { if (node.tagName == type) return node; }
  return null;
  }

/*****************************************************************************\
*            Part 2: MRBS-specific Active Cell Management routines            *
\*****************************************************************************/

// Define global variables that control the behaviour of the Active Cells.
// Set conservative defaults, to get the "classic" behaviour if JavaScript is half broken.

var useJS = false;	// If true, use JavaScript for cell user interface. If null, use a plain Anchor link.
var highlight_left_column = false;
var highlight_right_column = false;
var highlightColor = "#999999"; // Default highlight color, if we don't find the one in the CSS.
var statusBarMsg = "Click on the cell to make a reservation."; // Message to write on the status bar when activating a cell.
var areaType = 0;	// The effect of clicking and dragging. 0=None; 1=Rectangle; 2=Columns; 3=Rows.

// Duplicate at JavaScript level the relevant PHP configuration variables.
var show_plus_link = true;
var highlight_method = "hybrid";

var GetNodeColorClass = function(node)
    {
    return node.className;
    }
var SetNodeColorClass = function(node, colorClass) 
    { 
    node.className = colorClass;  // Use the TD.highlight color from mrbs.css.
    }

// Helper routines for searching text in the TD.highlight CSS class.
function SearchTdHighlightText(ruleText, ref)	// Callback called by the CSS scan routine
    {
    xbDump("SearchTdHighlightText() called back");
    if (!ruleText) return null;
    ruleText = ruleText.toLowerCase();			// Make sure search is using a known case.
    var k = ruleText.indexOf("td.highlight");
    if (k == -1) return null;				// TD.highlight not found in this rule.
    k = ruleText.indexOf("background-color:", k) + 17;
    if (k == 16) return null;				// TD.highlight background-color not defined.
    while (ruleText.charAt(k) <= ' ') k += 1;		// Strip blanks before the color value.
    var l = ruleText.length;
    var m = ruleText.indexOf(";", k);			// Search the separator with the next attribute.
    if (m == -1) m = l;
    var n = ruleText.indexOf("}", k);			// Search the end of the rule.
    if (n == -1) n = l;
    if (m < n) n = m;					// n = index of the first of the above two.
    while (ruleText.charAt(n-1) <= ' ') n -= 1; 	// Strip blanks after the color value
    var color = ruleText.substr(k, n-k);
    xbDump("SearchTdHighlightText() found color = " + color);
    return color;
    }
function isAlphaNum(c)
    {
    return ("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789".indexOf(c) >= 0);
    }
function SearchTdHighlight(rule, ref)	// Callback called by the CSS scan routine
    {
    if (!rule) return null;
    if (rule.selectorText)		// DOM. NS6, Konqueror.
	{
	var selector = rule.selectorText.toLowerCase();
        var i = selector.indexOf("td.highlight");
        if (i == -1) return null;
        if (i > 0) return null;
        // var c = selector.charAt(i+12);
        // if ((!c) || isAlphaNum(c)) return null;
	if (!rule.style) return null;
	return xblGetNodeBgColor(rule);
        }
    if (rule.cssText)			// Alternative for IE6
        return SearchTdHighlightText(rule.cssText);
    return null;
    }

/*---------------------------------------------------------------------------*\
*									      *
|   Function:       InitActiveCell					      |
|									      |
|   Description:    Initialize the active cell management.		      |
|									      |
|   Parameters:     Boolean show	Whether to show the (+) link.	      |
|                   Boolean left	Whether to highlight the left column. |
|                   Boolean right	Whether to highlight the right column.|
|                   String method	One of "bgcolor", "class", "hybrid".  |
|                   String message      The message to put on the status bar. |
|		    Integer area        0=None; 1=Rectangle; 2=Columns; 3=Rows.
|									      |
|   Returns:        Nothing.						      |
|									      |
|   Support:        NS4           No. Returns null.                           |
|                   IE5+, NS6+    Yes.                                        |
|                   Opera 6       Yes.                                        |
|									      |
|   Notes:          This code implements 3 methods for highlighting cells:    |
|		    highlight_method="bgcolor"				      |
|			Dynamically changes the cell background color.	      |
|			Advantage: Works with most javascript-capable browsers.
|			Drawback: The color is hardwired in this module.(grey)|
|		    highlight_method="class"				      |
|			Highlights active cells by changing their color class.|
|			The highlight color is the background-color defined   |
|			 in class td.highlight in the CSS.		      |
|			Advantage: The class definition in the CSS can set    |
|			 anything, not just the color.			      |
|			Drawback: Slooow on Internet Explorer 6 on slow PCs.  |
|		    highlight_method="hybrid"				      |
|			Extracts the color from the CSS DOM if possible, and  |
|			 uses it it like in the bgcolor method.		      |
|			Advantage: Fast on all machines; color defined in CSS.|
|			Drawback: Not as powerful as the class method.	      |
|									      |
|   History:                                                                  |
|									      |
|    2004/03/01 JFL Initial implementation.				      |
*									      *
\*---------------------------------------------------------------------------*/

function InitActiveCell(show, left, right, method, message, area)
    {
    show_plus_link = show;
    highlight_method = method;
    highlight_left_column = left;
    highlight_right_column = right;
    statusBarMsg = message;
    areaType = area;

    // document.write("<table id=\"test_table\" onClick=\"document.write(msg);\" border=1><h1>xbDump</h1></table>\n");

    xbDump("show_plus_link = " + show_plus_link);
    xbDump("highlight_method = " + highlight_method);
    xbDump("highlight_left_column = " + highlight_left_column);
    xbDump("highlight_right_column = " + highlight_right_column);
    xbDump("statusBarMsg = " + statusBarMsg);

    // Javascript feature detection: Check if a click handler can be called by the browser for a table.
    // For example Netscape 4 only supports onClick for forms.
    // For that, create a hidden table, and check if it has an onClick handler.
    // document.write("<table id=\"test_table\" onClick=\"return true\" border=0 style=\"display:none\"><tr><td id=\"test_td\" class=\"highlight\">&nbsp;</td></tr></table>\n");
    // Note: The previous line, also technically correct, silently breaks JavaScript on Netscape 4.
    //       (The processing of this file completes successfully, but the next script is not processed.)
    //       The next line, with the bare minimum content for our purpose, works on all browsers I've tested, included NS4.
    document.write("<table id=\"test_table\" onClick=\"return true\" border=0></table>\n");
    var test_table = xblGetElementById("test_table"); // Use this test table to detect the browser capabilities.
    if (test_table && test_table.onclick) useJS = true; // If the browser supports click event handlers on tables, then use JavaScript.

    xbDump("JavaScript feature detection: Table onClick supported = " + useJS);

    //----------------------------------------------------//

    //	Javascript feature detection: Check if the browser supports dynamically setting style properties.
    var useCssClass = ((highlight_method=="class") && test_table && test_table.style
                       && (test_table.style.setProperty || test_table.style.setAttribute) && true);
    if (useCssClass)			// DOM-compliant browsers
        GetNodeColorClass = function(node) 
	    {
            // xbDump("GetNodeColorClass<css>() returns " + (node.className ? node.className : "<undefined>"));
	    return node.className;
	    }
    else					// Pre-DOM browsers like Opera 6
        GetNodeColorClass = function(node)
	    {
	    color = xblGetNodeBgColor(node);
            // xbDump("GetNodeColorClass<dhtml>() returns " + (color ? color : "<undefined>"));
	    return color;
	    } // Can't get class, so get color.

    xbDump("JavaScript feature detection: Table class setting supported = " + useCssClass);

    //----------------------------------------------------//

    // Now search in the CSS objects the background color of the TD.highlight class.
    // This is done as a performance optimization for IE6: Only change the TD background color, but not its class.
    highlightColor = null;
    if (highlight_method!="bgcolor") highlightColor = xbForEachCssRule(SearchTdHighlight, 0);
    if (!highlightColor)
        {
        highlightColor = "#999999";	// Set default for DOM-challenged browsers
        xbDump("Using defaut highlight color = " + highlightColor);
        }
    else
        {
        xbDump("Found CSS highlight color = " + highlightColor);
        }

    //----------------------------------------------------//

    // Finally combine the last 2 results to generate the SetNodeColorClass function.
    if (useCssClass)			 // DOM-compliant browsers
        SetNodeColorClass = function(node, colorClass) 
            { 
            // xbDump("SetNodeColorClass<css>(" + colorClass + ")");
            node.className = colorClass;  // Use the TD.highlight color from mrbs.css.
            }
    else				 // Pre-DOM browsers like Opera 6
        SetNodeColorClass = function(node, colorClass) 
            {
            // xbDump("SetNodeColorClass<dhtml>(" + colorClass + ")");
            if (colorClass == "highlight") colorClass = highlightColor; // Cannot use the CSS color class. Use the color computed above.
            xblSetNodeBgColor(node, colorClass);
            }
    }

//----------------------------------------------------//

var rootCell = null;	// The first link cell clicked.
var firstCell = null;	// The top-left corner.
var lastCell = null;	// The bottom-right corner.

// Cell coloration
function HighlightNode(node)	// Change one TD cell color class -> highlight color.
    {
    if (!node.oldColorClassSet)
    {
	node.oldColorClassSet = true;
	node.oldColorClass = GetNodeColorClass(node); // Remember the initial color. (may be null)
	}
    SetNodeColorClass(node, "highlight");
    }
function LightOffNode(node)	// Change one TD cell color class -> initial color.
    {
    if (node.oldColorClassSet) SetNodeColorClass(node, node.oldColorClass);
    }

// Active side columns coloration
function SetSidesLight(tdCell, lightProc)	// Change TD cell + both side columns cells.
    {
    if (!tdCell) return;
    if (highlight_left_column)
        {
        // Locate the head node for the current row.
        var leftMostCell = xblFirstSibling(tdCell);
        if (leftMostCell) lightProc(leftMostCell);
        }
    if (highlight_right_column)
        {
        // Locate the last node for the current row. (Only when configured to display times at right too.)
        var rightMostCell = xblLastSibling(tdCell);
        // Now work around a Netscape peculiarity: The #text object is a sibling and not a child of the TD!
        while (rightMostCell && (rightMostCell.tagName != "TD")) rightMostCell = rightMostCell.previousSibling;
        if (rightMostCell) lightProc(rightMostCell);
        }
    }

// Active cell + side columns coloration
function SetNodesLight(tdCell, lightProc)	// Change TD cell + both side columns cells.
    {
    if (!tdCell) return;
    lightProc(tdCell);
    SetSidesLight(tdCell, lightProc);
    }

// Cross-link all cells together in all 4 directions.
function PrepareTable(cell)
        {
    var row = GetAncestor(cell, "TR");
    var table = GetAncestor(row, "TABLE");
    var rows = xblChildNodes(row.parentNode);
// xbDump("Preparing Table. There are "+rows.length+" rows.");
    var iRow=0
    var previousRow = null;
    for (var i=0; i<rows.length; i++)
        {
        row = rows[i];
// xbDump("Preparing row["+i+"] = " + GetNodePathname(row));
        if (GetNodeType(row) != "TR") continue;
        var cells = xblChildNodes(row);
        var iCol=0;
        var previousCell = null;
        var aboveCell = null;
        if (previousRow) 
            {
            aboveCell = previousRow.firstCell;
            previousRow.rowBelow = row;
            row.rowAbove = previousRow;
            }
        for (var j=0; j<cells.length; j++)
            {
            cell = cells[j];
// xbDump("Preparing cell["+i+","+j+"] = " + GetNodePathname(cell));
            if (GetNodeType(cell) != "TD") continue;
            if (!table.firstTdRow) table.firstTdRow = row;
            if (iCol == 0) row.firstCell = cell;
            if (aboveCell)
                {
                aboveCell.cellBelow = cell;
                cell.cellAbove = aboveCell;
                aboveCell = aboveCell.cellRight;
                }
            if (previousCell)
                {
                cell.cellLeft = previousCell;
                previousCell.cellRight = cell;
                }
            cell.iRow = iRow;
            cell.iCol = iCol;
            cell.prepared = true;
            previousCell = cell;
            iCol += 1;
            }
        previousRow = row;
        iRow += 1;
        }
    // Now MRBS-specific extensions.
    // First build a linear chain of entries, by row.
    var lastCell = null;
    var n = 0;
    for (row = table.firstTdRow; row; row = row.rowBelow)
        for (cell = row.firstCell; cell; cell = cell.cellRight)
            {
            if (highlight_left_column && (cell.iCol == 0)) continue; // The left time column is not part of the link.
            if (highlight_right_column && !cell.cellRight) continue; // The right time column, if present, isn't either.
            if (lastCell) lastCell.cellNextH = cell;
            cell.cellPrevH = lastCell;
            cell.ixH = n++;
            lastCell = cell;
            }
    xbDump("Found "+n+" cells in horizontal chain");
    // Then build a linear chain of entries, by column.
    var lastCell = null;
    var n = 0;
    for (var head = table.firstTdRow.firstCell; head; head = head.cellRight)
        {
        if (highlight_left_column && (head.iCol == 0)) continue; // The left time column is not part of the link.
        if (highlight_right_column && !head.cellRight) continue; // The right time column, if present, isn't either.
        for (cell = head; cell; cell = cell.cellBelow)
            {
            if (lastCell) lastCell.cellNextV = cell;
            cell.cellPrevV = lastCell;
            cell.ixV = n++;
            lastCell = cell;
            }
        }
    xbDump("Found "+n+" cells in vertical chain");
    }

function GrowRect(fromCell, proc1, proc2, n1, n2, sides)
    {
    // xbDump("GrowRect(" + GetNodePathname(fromCell) + ", " + proc1 + ", " + proc2 + ", " + n1 + ", " + n2 + ", " + sides + ")");
    for (c1 = eval("fromCell.cell"+proc1); n1; --n1 && (c1 = eval("c1.cell"+proc1)))
        for (c2 = c1, n = n2; n; --n && (c2 = eval("c2.cell"+proc2)))
            if (sides)
                SetNodesLight(c2, HighlightNode);
            else
                HighlightNode(c2);
    return c1;
        }

function ShrinkRect(fromCell, proc1, proc2, n1, n2, sides)
        {
    // xbDump("ShrinkRect(" + GetNodePathname(fromCell) + ", " + proc1 + ", " + proc2 + ", " + n1 + ", " + n2 + ", " + sides + ")");
    for (c1 = fromCell; n1; --n1 && (c1 = eval("c1.cell"+proc1)))
        for (c2 = c1, n = n2; n; --n && (c2 = eval("c2.cell"+proc2)))
            if (sides)
                SetNodesLight(c2, LightOffNode);
            else
                LightOffNode(c2);
    return eval("c1.cell"+proc1);
    }

function GrowChain(from, to, proc)
    {
    // xbDump("GrowChain(" + GetNodePathname(from) + ", " + GetNodePathname(to) + ", " + proc + ")");
    if (from.iRow != to.iRow)
	{
	SetSidesLight(from, LightOffNode);
	SetSidesLight(to, HighlightNode);
	}
    while (from != to)
	{
	from = eval("from.cell"+proc);
        HighlightNode(from);
	}
    return to;
    }

function ShrinkChain(from, to, proc)
    {
    // xbDump("ShrinkChain(" + GetNodePathname(from) + ", " + GetNodePathname(to) + ", " + proc + ")");
    if (from.iRow != to.iRow)
	{
	SetSidesLight(from, LightOffNode);
	SetSidesLight(to, HighlightNode);
	}
    while (from != to)
	{
        LightOffNode(from);
	from = eval("from.cell"+proc);
	}
    return to;
    }

// Cell activation
function ActivateCell(cell)	// Activate the TD cell under the mouse, and optionally the corresponding hour cells on both sides of the table.
    {
    xbDump("ActivateCell(" + GetNodePathname(cell) + ")");
    // Find the enclosing table data cell, since we fired on the hidden inner table.
    td = GetAncestor(cell, "TD");
    if (!td.prepared) PrepareTable(td);
    if (td.isActive) return;	// Prevent problems with reentrancy. (It happens on slow systems)
    td.isActive = true;
    if (statusBarMsg) window.status = statusBarMsg; // Write into the status bar.
    // Highlight the cells and exit, unless we're in a click-drag operation.
    if (!rootCell) { SetNodesLight(td, HighlightNode); return; }
    // Else we're in a click-drag operation. Update the area.
    switch (areaType)
	{
	case 1:	// Rectangle.
    if (td.iRow < firstCell.iRow)					// Top side grew?
	firstCell = GrowRect(firstCell, "Above", "Right", firstCell.iRow-td.iRow, lastCell.iCol+1-firstCell.iCol, 1);
    else if (td.iRow > lastCell.iRow)					// Bottom side grew?
	lastCell = GrowRect(lastCell, "Below", "Left",td.iRow-lastCell.iRow, lastCell.iCol+1-firstCell.iCol, 1);
    else if ((firstCell.iRow < td.iRow) && (td.iRow <= rootCell.iRow))	// Top side shrank?
	firstCell = ShrinkRect(firstCell, "Below", "Right", td.iRow-firstCell.iRow, lastCell.iCol+1-firstCell.iCol, 1);
    else if ((rootCell.iRow <= td.iRow) && (td.iRow < lastCell.iRow))	// Bottom side shrank?
	lastCell = ShrinkRect(lastCell, "Above", "Left", lastCell.iRow-td.iRow, lastCell.iCol+1-firstCell.iCol, 1);

    if (td.iCol < firstCell.iCol)					// Left side grew?
	firstCell = GrowRect(firstCell, "Left", "Below", firstCell.iCol-td.iCol, lastCell.iRow+1-firstCell.iRow, 0);
    else if (td.iCol > lastCell.iCol)					// Right side grew?
	lastCell = GrowRect(lastCell, "Right", "Above", td.iCol-lastCell.iCol, lastCell.iRow+1-firstCell.iRow, 0);
    else if ((firstCell.iCol < td.iCol) && (td.iCol <= rootCell.iCol))	// Left side shrank?
	firstCell = ShrinkRect(firstCell, "Right", "Below", td.iCol-firstCell.iCol, lastCell.iRow+1-firstCell.iRow, 0);
    else if ((rootCell.iCol <= td.iCol) && (td.iCol < lastCell.iCol))	// Right side shrank?
	lastCell = ShrinkRect(lastCell, "Left", "Above", lastCell.iCol-td.iCol, lastCell.iRow+1-firstCell.iRow, 0);
	    break;

	case 2:	// Columns.
    if ((lastCell == rootCell) && (td != firstCell))			// Beginning moved?
        {
        if (td.ixV < firstCell.ixV)					    // Beginning grew?
            firstCell = GrowChain(firstCell, td, "PrevV");
	else if (td.ixV < rootCell.ixV)					    // Shrank towards the center?
	    firstCell = ShrinkChain(firstCell, td, "NextV");
	else								    // Else moved right past the center.
	    {
	    firstCell = ShrinkChain(firstCell, rootCell, "NextV");
	    lastCell = GrowChain(rootCell, td, "NextV");
	    }
        }
    else if ((firstCell == rootCell) && (td != lastCell))		// End Moved?
        {
        if (td.ixV > lastCell.ixV)					    // End grew?
            lastCell = GrowChain(lastCell, td, "NextV");
	if (td.ixV > rootCell.ixV)					    // Shrank towards the center?
	    lastCell = ShrinkChain(lastCell, td, "PrevV");
	else								    // Else moved left past the center.
	    {
	    lastCell = ShrinkChain(lastCell, rootCell, "PrevV");
	    firstCell = GrowChain(rootCell, td, "PrevV");
        }
    }
    else xbDump("No column move needed");
	    break;

	case 3:	// Rows.
    if ((lastCell == rootCell) && (td != firstCell))			// Beginning moved?
        {
        if (td.ixH < firstCell.ixH)					    // Beginning grew?
            firstCell = GrowChain(firstCell, td, "PrevH");
	else if (td.ixH < rootCell.ixH)					    // Shrank towards the center?
	    firstCell = ShrinkChain(firstCell, td, "NextH");
	else								    // Else moved right past the center.
	    {
	    firstCell = ShrinkChain(firstCell, rootCell, "NextH");
	    lastCell = GrowChain(rootCell, td, "NextH");
	    }
        }
    else if ((firstCell == rootCell) && (td != lastCell))		// End Moved?
        {
        if (td.ixH > lastCell.ixH)					    // End grew?
            lastCell = GrowChain(lastCell, td, "NextH");
	if (td.ixH > rootCell.ixH)					    // Shrank towards the center?
	    lastCell = ShrinkChain(lastCell, td, "PrevH");
	else								    // Else moved left past the center.
	    {
	    lastCell = ShrinkChain(lastCell, rootCell, "PrevH");
	    firstCell = GrowChain(rootCell, td, "PrevH");
	    }
	}
    else xbDump("No row move needed");
	    break;

	default:
	    break;
	}
    }

// Cell unactivation
function UnactivateCell(cell)
    {
    xbDump("UnactivateCell(" + GetNodePathname(cell) + ")");
    // Find the enclosing table data cell, since we fired on the hidden inner table.
    td = GetAncestor(cell, "TD");
    if (!td.isActive) return; // Prevent problems with reentrancy.
    td.isActive = null;
    window.status = "";		// Clear the status bar.
    // Remove the highlight colors, unless we're in a click-drag operation.
    if (!rootCell) SetNodesLight(td, LightOffNode);
    }

xbDump("Cell activation routines defined.");

//----------------------------------------------------//

// Cell click handling

// Callback used to find the A link inside the cell clicked.
function GetLinkCB(node, ref)
    {
    var tag = null;
    if (node.tagName) tag = node.tagName;		// DOM-compliant tag name.
    else if (node.nodeName) tag = node.nodeName;	// Implicit nodes, such as #text.
    if (tag && (tag.toUpperCase() == "A")) return node;
    return null;
    }

function GetLink(node)
    {
    link = ForEachDescendant(node, GetLinkCB, null);
    if (!link) return null;
    return link.href;
    }

// Handler for going to the period reservation edition page.
function GotoLink(node)
    {
    xbDump("GotoLink(" + GetNodePathname(node) + ")");
    // Sometimes, we miss the mouseUp event. (IE6 sometimes, and Opera 6 always)
    // Allow clicking in the active area to validate the selected area.
    if (rootCell) MarkLastCell(node);
    // Normal case: Follow the link.
    link = GetLink(node);
    if (link) window.location = link;
    }

xbDump("Cell click handlers defined.");

//----------------------------------------------------//

function MarkFirstCell(cell) // Invoked onMouseDown events.
    {
    xbDump("MarkFirstCell(" + GetNodePathname(cell) + ")");
    if (!rootCell) // Avoid doing it twice (Fix for Opera 6 where a mouseDown event occurs before every click event).
	{
        // Find the enclosing table data cell, since we fired on the hidden inner table.
        cell = GetAncestor(cell, "TD");
        rootCell = firstCell = lastCell = cell;
	}
    return false; // Prevent browser default drag action.
    }

function MarkLastCell(cell) // Invoked onMouseUp events.
    {
    xbDump("MarkLastCell(" + GetNodePathname(cell) + ")");
    var link = null;
    if (rootCell) // Don't do anything if MouseDown occured out of the active zone.
	{
        // Find the enclosing table data cell, since we fired on the hidden inner table.
        var tdCell = GetAncestor(cell, "TD");
        if ((rootCell == firstCell) && (rootCell == lastCell) && (rootCell != tdCell))
            { // Some browsers (IE5, O6) don't generate mouse move events while the button is down.
            ActivateCell(cell);       // So in this case, record the movement done.
            }
        // Build the link to the reservation edit page.
        link = GetLink(firstCell);
        link += "&nperiods=" + (lastCell.iRow + 1 - firstCell.iRow);
        link += "&nrooms=" + (lastCell.iCol + 1 - firstCell.iCol);
        link += "&shape=" + areaType;
        // Erase the highlighted zone. This allows reusing the page later on.
        switch (areaType)
            {
            case 1: // Rectangle.
                ShrinkRect(firstCell, "Right", "Below", lastCell.iCol+1-firstCell.iCol, lastCell.iRow+1-firstCell.iRow, 1);
                break;
            case 2: // Vertical chain.
                ShrinkChain(firstCell, lastCell, "NextV");
                break;
            case 3: // Horizontal chain.
                ShrinkChain(firstCell, lastCell, "NextH");
                break;
            default:
                break;
            }
        }
    // Cleanup state variables.
    rootCell = firstCell = lastCell = null;
    // And finally jump to the reservation edit page.
    if (link) window.location = link;
    }

//----------------------------------------------------//

// Cell content generation

function BeginActiveCell()
    {
    if (useJS)
        {
        document.write("<table class=\"naked\" width=\"100%\" cellSpacing=\"0\" onMouseOver=\"ActivateCell(this)\" onMouseOut=\"UnactivateCell(this)\" onMouseDown=\"MarkFirstCell(this)\" onMouseUp=\"MarkLastCell(this)\" onClick=\"GotoLink(this)\">\n<td class=\"naked\">\n");
	// Note: The &nbsp; below is necessary to fill-up the cell. Empty cells behave badly in some browsers.
        if (!show_plus_link) document.write("&nbsp;<div style=\"display:none\">\n"); // This will hide the (+) link.
        }
    }

function EndActiveCell()
    {
    if (useJS)
        {
        if (!show_plus_link) document.write("</div>");
        document.write("</td></table>\n");
        }
    }

xbDump("Cell content generation routines defined.");

//----------------------------------------------------//

if (doAlert) alert("Ended xbLib.js");
xbDump("Ended xbLib.js.php");
