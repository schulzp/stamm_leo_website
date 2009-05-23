///////////////////////////////////////////////////////////////////////////////////////////////////
// Author: Scott Lewis
// Date:   Nov 10, 2007
///////////////////////////////////////////////////////////////////////////////////////////////////

function CurrencyFormatted(amount)
{
    var i = parseFloat(amount);
    if(isNaN(i)) { i = 0.00; }
    var minus = '';
    if(i < 0) { minus = '-'; }
    i = Math.abs(i);
    i = parseInt((i + .005) * 100);
    i = i / 100;
    s = new String(i);
    if(s.indexOf('.') < 0) { s += '.00'; }
    if(s.indexOf('.') == (s.length - 2)) { s += '0'; }
    s = minus + s;
    return new String(s);
};

function UserAgentDetails()
{
    var ua = navigator;
    var details = "";
    for (k in ua)
    {
        if (typeof ua[k] == "string")
        {
            details += k + ": " + ua[k] + "\n";
        }
    }
    return details;
};

function ObjToXML(obj)
{
    var xml = "";
    for (key in obj)
    {
        if (typeof obj[key] == "string")
        {
            xml += "<" + key.toLowerCase() + ">" + obj[key] + "</" + key + ">\n";
        }
    }
    return xml;
};

function ObjToJSON(obj)
{
    var json = "{";
    for (key in obj)
    {
        if (typeof obj[key] == "string")
        {
            json += "'" + key + "': '" + obj[key] + "', ";
        }
    }
    json += "}";
    return json;
};


function GetVar(arr, key, _default)
{
    if (typeof(arr[key]) != "undefined")
    {
        return arr[key];
    }
    return _default;
};

function LoadScript(src)
{
    $j.ajax({
        url: src,
        success: function(txt) {eval(txt);},
        async: false
    });
};

function GetJSON(src)
{
    return $j.ajax({
        url: src,
        async: false
    }).responseText;
};

function getOffsets(evt) 
{
    if ($j.browser.msie)
    {
        return {
            offsetX: evt.offsetX, 
            offsetY: evt.offsetY
        };
    }
    var target = evt.target;
    if (typeof target.offsetLeft == 'undefined') 
    {
        target = target.parentNode;
    }
    var pageCoords = getPageCoords(target);
    var eventCoords = { 
        x: window.pageXOffset + evt.clientX,
        y: window.pageYOffset + evt.clientY
    };
    var offsets = {
        offsetX: eventCoords.x - pageCoords.x,
        offsetY: eventCoords.y - pageCoords.y
    }
    return offsets;
};

function getPageCoords (element) 
{
    var coords = {x : 0, y : 0};
    while (element) 
    {
        coords.x += element.offsetLeft;
        coords.y += element.offsetTop;
        element = element.offsetParent;
    }
    return coords;
};

//////////////////////////////////////////////////////////////////////////////////////////////
// jQuery Extensions
//////////////////////////////////////////////////////////////////////////////////////////////

jQuery.trim = function(str) {
	if (!str) return str;
    var str = str.replace(/^\s+|\s+$j/g, '');
    return str = str.replace(/^\t+|\t+$j/g, '');
};

jQuery.empty = function(str) {
    return jQuery.trim(str) == "";    
};

jQuery.validate = function(vtype, val) {
    switch (vtype)
    {
        case 'zip':
            return IsZipCode(val);
            break;
    }    
};

function IsZipCode(zip)
{
    var plus4 = null;
    if (!zip) return false;
    if (zip.indexOf('-') != -1)
    {
        var bits  = zip.split('-');
        var zip   = bits[0];
        var plus4 = bits[1];
    }
    if (zip.length != 5 || !$j.isNumber(zip))
    {
        return false;
    }
    if (plus4 && (plus4.length != 4 || !$j.isNumber(plus4)))
    {
        return false;
    }
    return true;
};

jQuery.isNumber = function(n) {
    var ints = "0123456789";
    for (i=0; i<n.length; i++)
    {
        if (ints.indexOf(n.charAt(i)) < 0)
        {
            return false;
        }
    }
    return true;
};

jQuery.fn.extend({
    hover: function() {
        var _class = $j(this).attr("class");
        if (typeof _class == "undefined" || 
            _class.indexOf("hasHover") == -1)
        {
            $j(this).attr({"class": _class + "_hover"});
        }
        else
        {
            $j(this).attr({"class": _class.replace("_hover", "")});
        }
    }
});

jQuery.fn.extend({
    imageReplace: function() {
        var txt = $j(this).text();
        $j(this).text("");
        $j(this).append("<span class=\"hide\">" + txt + "</span>\n");
    }
});

jQuery.fn.extend({
    checked: function() {
        return $j(this).attr("checked") == true;
    },
    check: function() {
        $j(this).attr({"checked": "checked"});
    },
    uncheck: function() {
        $j(this).attr({"checked": false});
    },
    disable: function() {
        $j(this).attr({"disabled": true});
    },
    enable: function() {
        $j(this).attr({"disabled": false});
    }
});

jQuery.uniqueID = function() {
    return Math.floor(Math.random() * 1000);
};

jQuery.fn.onkey = function(key, callback)
{
    this.keypress(function(e) {
        if ((e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0) == key)
        {
            if (typeof callback == "function")
            {
                return callback();
            }
        }
        return true;
    });
    return this;
}

// jQuery JSON Plug-In

$j.json = {callbacks: {}};

$j.fn.json = function( url, callback ) {
    var _$j_ = this;
    load( url.replace( /{callback}/, name(callback) ) );
    return this;

    function name( callback ) {
        var id = (new Date).getTime();
        var name = 'json_' + id;

        var cb = $j.json.callbacks[id] = function( json ) {
            delete $j.json.callbacks[id];
            eval( 'delete ' + name );
            _$j_.each( function() { callback(json); } );
        };

        eval( name + ' = cb' );
        return name;
    }

    function load( url ) {
        var script = document.createElement( 'script' );
        script.type = 'text/javascript';
        script.src = url;
        $j('head',document).append( script );
    }
};

jQuery.extend({
 getVar: function(strParamName, strHref) {
      var strReturn = "";
      var bFound    =false;
      
      var cmpstring = strParamName + "=";
      var cmplen = cmpstring.length;

      if ( strHref.indexOf("?") > -1 ){
        var strQueryString = strHref.substr(strHref.indexOf("?")+1);
        var aQueryString = strQueryString.split("&");
        for ( var iParam = 0; iParam < aQueryString.length; iParam++ ){
          if (aQueryString[iParam].substr(0,cmplen)==cmpstring){
            var aParam = aQueryString[iParam].split("=");
            strReturn = aParam[1];
            bFound=true;
            break;
          }
          
        }
      }
      if (bFound==false) return null;
      return strReturn;
    }
});

/* Copyright (c) 2006 Mathias Bank (http://www.mathias-bank.de)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * 
 * Thanks to Hinnerk Ruemenapf - http://hinnerk.ruemenapf.de/ for bug reporting and fixing.
 */
jQuery.extend({
/**
* Returns get parameters.
*
* If the desired param does not exist, null will be returned
*
* @example value = $j.getURLParam("paramName");
*/ 
 getURLParam: function(strParamName) {
      var strReturn = "";
      var strHref   = new String(window.location.href);
      var bFound    =false;
      
      var cmpstring = strParamName + "=";
      var cmplen = cmpstring.length;

      if ( strHref.indexOf("?") > -1 ){
        var strQueryString = strHref.substr(strHref.indexOf("?")+1);
        var aQueryString = strQueryString.split("&");
        for ( var iParam = 0; iParam < aQueryString.length; iParam++ ){
          if (aQueryString[iParam].substr(0,cmplen)==cmpstring){
            var aParam = aQueryString[iParam].split("=");
            strReturn = aParam[1];
            bFound=true;
            break;
          }
          
        }
      }
      if (bFound==false) return null;
      return strReturn;
    },
    getParentURLParam: function(strParamName) {
      var strReturn = "";
      var strHref = window.parent.location.href;
      var bFound=false;
      
      var cmpstring = strParamName + "=";
      var cmplen = cmpstring.length;

      if ( strHref.indexOf("?") > -1 ){
        var strQueryString = strHref.substr(strHref.indexOf("?")+1);
        var aQueryString = strQueryString.split("&");
        for ( var iParam = 0; iParam < aQueryString.length; iParam++ ){
          if (aQueryString[iParam].substr(0,cmplen)==cmpstring){
            var aParam = aQueryString[iParam].split("=");
            strReturn = aParam[1];
            bFound=true;
            break;
          }
          
        }
      }
      if (bFound==false) return null;
      return strReturn;
    }
});

jQuery.extend({
    getPageName: function() {
        var bits = new String(window.location.href).split("/");
        if (bits.length)
        {
            var page = bits[bits.length-1];
            if (page.indexOf('?') != -1)
            {
                var bits = page.split('?');
                var page = bits[0];
            }
            return page;
        }
        return "";
    }
});