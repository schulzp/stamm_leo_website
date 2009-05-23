$j.mark = function(field)
{
    $j(field).css({
        "border": "2px solid #BD171E",
        "background-color": "#FEE"
    });
};

$j.marklabel = function(_for)
{
    $j("label[@for="+_for+"]").css({
        "color": "#BD171E"
    });
};

$j.unmark = function(field)
{
    $j("#"+$j(field).attr("id")).css({
        "border": "",
        "background-color": ""
    });
};

$j.validate = function(field, validation, match, strict)
{
    switch (validation)
    {
        case 'notnull':
            return !$j.isempty($j(field).val());
            break;
        case 'email':
            return $j.email($j(field).val());
            break;
        case 'number':
            return $j.isnumber($j(field).val());
            break;
        case 'length':
            return $j.islength($j(field).val(), match, strict);
            break;
        case 'phone':
            return $j.phone($j(field).val());
            break;
        case 'zip':
            return $j.zip($j(field).val());
            break;
    }
};

$j.error = function(str)
{
    $j("#message").text(str);
    $j("#message").slideDown("slow");
};

$j.FieldIsEmpty = function(field) {
    return $j.isempty($j(field).val());
};

$j.isempty = function(str) {
    return $j.trim(str) == "";
};

$j.trim = function(str) {
    str = str.replace(/^\s*|\s*$j/g,"");
    str = str.replace(/^\t*|\t*$j/g,"");
    str = str.replace(/^\r*|\r*$j/g,"");
    str = str.replace(/^\n*|\n*$j/g,"");
    return str;
};

$j.isnumber = function (n) {
    if (isNaN(n))
    {
        return false;
    }
    return true;
};

$j.islength = function (str, len, exact) {
    if ($j.isempty(str))
    {
        return false;
    }
    if (str.length < len)
    {
        return false;
    }
    else if (exact && str.length != len)
    {
        return false;
    }
    return true;
};

$j.zip = function(str)
{
    if ($j.isempty(str))
    {
        return false;
    }

    var zip   = str;
    var plus4 = "";
    if (str.indexOf('-') != -1)
    {
        var bits = str.split('-');
        zip = bits[0];
        plus4 = bits[1];
    }
    if (!$j.islength(zip, 5, true))
    {
        return false;
    }
    if (!$j.isnumber(zip))
    {
        return false;
    }
    if (plus4 != "")
    {
	    if (!$j.islength(plus4, 4, true))
	    {
	        return false;
	    }
	    if (!$j.isnumber(plus4))
	    {
	        return false;
	    }
    }
    return true;
};

$j.phone = function(str)
{
    if ($j.isempty(str))
    {
        return false;
    }
    var str = str.replace(/\(/g, '');
        str = str.replace(/\)/g, '');
        str = str.replace(/-/g, '');
        str = str.replace(/\./g, '');
        str = str.replace(/\s/g, '');
    if (!$j.islength(str, 10, true))
    {
        return false;
    }
    if (!$j.isnumber(str))
    {
        return false;
    }
    return true;
};

$j.email = function(str) {
    if ($j.isempty(str))
    {
        return false;
    }

    var at   = "@";
    var dot  = ".";
    var lat  = str.indexOf(at);
    var lstr = str.length;
    var ldot = str.indexOf(dot);
    
    if (str.indexOf(at) == -1) 
    {
       return false;
    }

    if (str.indexOf(at) == -1 || 
        str.indexOf(at) == 0 || 
        str.indexOf(at) == lstr)
    {
       return false;
    }

    if (str.indexOf(dot) == -1 || 
        str.indexOf(dot) == 0 || 
        str.indexOf(dot) == lstr)
    {
        return false;
    }

     if (str.indexOf(at, (lat+1)) != -1)
     {
        return false;
     }

     if (str.substring(lat-1, lat) == dot || 
         str.substring(lat+1, lat+2) == dot)
     {
        return false;
     }

     if (str.indexOf(dot, (lat+2)) == -1)
     {
        return false;
     }
    
     if (str.indexOf(" ") != -1)
     {
        return false;
     }
     return true;;                
};
