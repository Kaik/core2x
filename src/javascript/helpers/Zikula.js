// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

if (typeof(Zikula) == 'undefined') {
    /**
     * Zikula global object
     * 
     * @namespace Zikula global object
     * 
     * @borrows Zikula.Gettext#getMessage as #__
     * @borrows Zikula.Gettext#getMessageFormatted as #__f
     * @borrows Zikula.Gettext#getPluralMessage as #_n
     * @borrows Zikula.Gettext#getPluralMessageFormatted as #_fn
     */
    Zikula = {};
}

/**
 * Global Zikula config object.
 * Zikula.Config is defined inline in HTML HEAD and is always avaiable.<br >
 * Contains following properties:<br >
 * - entrypoint<br >
 * - baseURL<br >
 * - baseURI<br >
 * - ajaxtimeout<br >
 * - lang
 *
 * @name Zikula.Config
 */

/**
 * Creates namespace in Zikula scope through nested chain of objects, based on the given path
 * Example:
 * Zikula.define('Module.Component') will create object chain: Zikula.Module.Component
 * If object in chain already exists it will be extended, not overwritten
 * 
 * @param {string} path Dot separated path to define.
 * 
 * @return {object} Zikula extended object
 */
Zikula.define = function(path) {
    return path.split('.').inject(Zikula, function(object, prop) {
        return object[prop] = object[prop] || { };
    })
}

/**
 * Load what's needed on dom loaded.
 *
 * @return void
 */
Zikula.init = function()
{
    if(Zikula.Browser.IE) {
        Zikula.fixbuttons();
    }
}
document.observe('dom:loaded',Zikula.init);

/**
 * Extends prototype Browser detection.
 * Adds following properties to original ones:<br />
 * - IES.IE8 - true when IE 8<br />
 * - IES.IE8e7 - true when IE8 is in IE7 mode<br />
 * - IES.IE7 - true when IE7 or IE8 in IE7 mode
 * (means that for IE8 in IE7 mode both - IE7 and IE8e7 are true)<br />
 * - IES.IE6 - true for IE6 or older
 *
 * @return {object} Object with browsers info
 */
Zikula.Browser = (function(){
    var IES = {IE6:false,IE7:false,IE8:false,IE8e7:false};
    if(Prototype.Browser.IE) {
        if (document.documentMode != 'undefined' && document.documentMode == 8) {
            IES.IE8 = true;
        } else if (typeof document.documentElement.style.maxHeight != 'undefined'){
            IES.IE7 = true;
            IES.IE8e7 = (typeof document.documentMode != 'undefined'); //IE8 in IE7 mode
        } else {
            IES.IE6 = true;
        }
    }
    return Object.extend(IES,Prototype.Browser);
  })()

/**
 * Decodes json data to original format
 *
 * @param {string} jsondata JSONized array in utf-8 (as created by AjaxUtil::output).
 *
 * @return {mixed} Decoded data
 */
Zikula.dejsonize = function(jsondata)
{
    var result;
    try {
        result = jsondata.evalJSON(true);
    } catch(error) {
        alert('illegal JSON response: \n' + error + 'in\n' + jsondata);
    }
    return result;
}

/**
 * Shows an error message with alert().
 *
 * @todo beautify this
 *
 * @param {string} errortext The text to show.
 *
 * @return void
 */
Zikula.showajaxerror = function(errortext)
{
    alert(errortext);
    return;
}

/**
 * Manage ajax error responses returned by AjaxUtil.
 *
 * @param {object}  transport      Transport object returned by Ajax.Request.
 * @param {bool}    [supresserror] Should error message be supressed.
 *
 * @return {mixed} Decoded transport data or void
 */
Zikula.ajaxResponseError = function(transport, supresserror)
{
	var json = pndejsonize(transport.responseText);
	if ("authid" in json) {
		if (json.authid != '') {
		    Zikula.updateauthids(json.authid);
		}
	}
	if (json.displayalert == '1' && supresserror != true) {
		Zikula.showajaxerror(json.errormessage);
	}
	return json;
}

/**
 * Sets a select to a given value.
 *
 * @param {string} id  Select id.
 * @param {string} sel The value that should be selected.
 *
 * @return void
 */
Zikula.setselectoption = function(id, sel)
{
    $A($(id).options).each(function(opt){opt.selected = (opt.value == sel);});
}

/**
 * Zikula.getcheckboxvalue
 * Gets the value of a checkbox depending on the state.
 *
 * @deprecated Use Prototype $F
 *
 * @param {string} id Checkbox id.
 *
 * @return {string} Checkbox value
 */
Zikula.getcheckboxvalue = function(id)
{
    return $F(id) || '';
}

/**
 * Updates all hidden authid fields with a new authid obtained with an ajax call.
 *
 * @param {string} authid The new authid.
 * 
 * @return void
 */
Zikula.updateauthids = function(authid)
{
    if(authid.length != 0) {
        $$('form input[name=authid]').invoke('writeAttribute','value',authid);
    }
    return;
}

/**
 * Set z-odd / z-even on each li after append, move and delete.
 *
 * @param   {string} listclass   Class applied to the list of items.
 * @param   {string} headerclass Class applied to the header of the list.
 * 
 * @return  void
 */
Zikula.recolor = function(listclass, headerclass)
{
    var pnodd = true;

    $A($(listclass).childNodes).each(
        function(node)
        {
            if (Element.hasClassName(node, headerclass)) {
            } else {
                Element.removeClassName(node, 'z-odd');
                Element.removeClassName(node, 'z-even');

                if (pnodd == true) {
                    Element.addClassName(node, 'z-odd');
                } else {
                    Element.addClassName(node, 'z-even');
                }
                pnodd = !pnodd;
            }
        }
        );
}

/**
 * Change the display attribute of an specific object.
 *
 * @param   {string} id Id of the object to hide/show.
 * 
 * @return  void
 */
Zikula.switchdisplaystate = function(id)
{
    var pntmpobj = $(id);

    if (pntmpobj.getStyle('display') == 'none') {
        if (typeof(Effect) != "undefined") {
            Effect.BlindDown(pntmpobj);
        } else {
            pntmpobj.show();
        }
    } else {
        if (typeof(Effect) != "undefined") {
            Effect.BlindUp(pntmpobj);
        } else {
            pntmpobj.hide();
        }
    }
}

/**
 * Change the display attribute of an specific container depending of a radio input.
 *
 * @param  {string} idgroup       Id of the container where the radio input to observe are.
 * @param  {string} idcontainer   Id of the container to hide/show.
 * @param  {bool}   state         State of the radio to show the idcontainer.
 *
 * @return void
 */
Zikula.radioswitchdisplaystate = function(idgroup, idcontainer, state)
{
    var objgroup = $(idgroup);
    var objcont = $(idcontainer);

    check_state = objgroup.select('input[type=radio][value="1"]').pluck('checked').any();

    if (check_state == state) {
        if (objcont.getStyle('display') == 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindDown(objcont);
            } else {
                objcont.show();
            }
        }
    } else {
        if (objcont.getStyle('display') != 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindUp(objcont);
            } else {
                objcont.hide();
            }
        }
    }
}

/**
 * Change the display attribute of an specific container depending of a checkbox input.
 *
 * @param  {string} idcheckbox    Id of the checkbox input to observe.
 * @param  {string} idcontainer   Id of the container to hide/show.
 * @param  {bool}   state         State of the checkbox to show the idcontainer.
 *
 * @return void
 */
Zikula.checkboxswitchdisplaystate = function(idcheckbox, idcontainer, state)
{
    var objcont = $(idcontainer);

    check_state = !!$F(idcheckbox);

    if (check_state == state) {
        if (objcont.getStyle('display') == 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindDown(objcont);
            } else {
                objcont.show();
            }
        }
    } else {
        if (objcont.getStyle('display') != 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindUp(objcont);
            } else {
                objcont.hide();
            }
        }
    }
}

/**
 * Workaround for wrong buttons values in IE and multiple submit buttons in IE6/7.
 *
 * @return void
 */
Zikula.fixbuttons = function()
{
    $$('button').invoke('observe','click',function(e){
        var form = e.element().up('form');
        if(form) {
            form.store('buttonClicked',e.element().identify());
        }
    });

    $$('form').invoke('observe','submit',function(e){
        var form = e.element(),
            buttonClicked = form.retrieve('buttonClicked',null);
        form.select('button').each(function(b){
            b.disabled = true;
            if(b.identify() == buttonClicked) {
                form.insert(new Element('input',{type:'hidden',name:b.name,value:b.attributes.getNamedItem('value').nodeValue}));
            }
        });
    });
}

/**
 * Ajax timeout detection. We set the time out to 5 seconds
 * taken from http://codejanitor.com/wp/2006/03/23/ajax-timeouts-with-prototype/
 *
 * @param {object} xmlhttp Transport object returned by Ajax.Request.
 *
 * @return void
 */
Zikula.callInProgress = function(xmlhttp) {
    switch (xmlhttp.readyState) {
        case 1:
        case 2:
        case 3:
            return true;
            break;
        // Case 4 and 0
        default:
            return false;
            break;
    }
}

// Register global responders that will occur on all AJAX requests
Ajax.Responders.register({
    onCreate: function(request) {
        if($('ajax_indicator')) {
            Element.show('ajax_indicator');
        }
        request['timeoutId'] = window.setTimeout(
            function() {
                // If we have hit the timeout and the AJAX request is active, abort it and let the user know
                if (Zikula.callInProgress(request.transport)) {
                    request.transport.abort();
                    if($('ajax_indicator') && $('ajax_indicator').tagName == 'IMG') {
                        $('ajax_indicator').src = Zikula.Config.baseURL + 'images/icons/extrasmall/error.gif';
                    }
                    pnshowajaxerror('Ajax connection time out!');
                    // Run the onFailure method if we set one up when creating the AJAX object
                    if (request.options['onFailure']) {
                        request.options['onFailure'](request.transport, request.json);
                    }
                }
            },
            (typeof(Zikula.Config.ajaxtimeout)!='undefined' && Zikula.Config.ajaxtimeout!=0)  ? Zikula.Config.ajaxtimeout : 5000 // per default five seconds - can be changed in the settings
        );
    },
    onComplete: function(request) {
        if($('ajax_indicator')) {
            Element.hide('ajax_indicator');
        }
        // Clear the timeout, the request completed ok
        window.clearTimeout(request['timeoutId']);
    }
});


/**
 * @deprecated Use {@link Zikula.dejsonize}
 */
function pndejsonize(jsondata)
{
    return Zikula.dejsonize(jsondata)
}

/**
 * @deprecated Use {@link Zikula.showajaxerror}
 */
function pnshowajaxerror(errortext)
{
    return Zikula.showajaxerror(errortext);
}

/**
 * @deprecated Use {@link Zikula.setselectoption}
 */
function pnsetselectoption(id, sel)
{
    return Zikula.setselectoption(id, sel);
}

/**
 * @deprecated Use {@link Zikula.getcheckboxvalue}
 */
function pngetcheckboxvalue(id)
{
    return Zikula.getcheckboxvalue(id);
}

/**
 * @deprecated Use {@link Zikula.updateauthids}
 */
function pnupdateauthids(authid)
{
    return Zikula.updateauthids(authid);
}

/**
 * @deprecated Use {@link Zikula.callInProgress}
 */
function callInProgress(xmlhttp)
{
    return Zikula.callInProgress(xmlhttp);
}

/**
 * @deprecated Use {@link Zikula.recolor}
 */
function pnrecolor(listclass, headerclass)
{
    return Zikula.recolor(listclass, headerclass);
}

/**
 * @deprecated Use {@link Zikula.switchdisplaystate}
 */
function switchdisplaystate(id)
{
    return Zikula.switchdisplaystate(id);
}

/**
 * @deprecated Use {@link Zikula.radioswitchdisplaystate}
 */
function radioswitchdisplaystate(idgroup, idcontainer, state)
{
    return Zikula.radioswitchdisplaystate(idgroup, idcontainer, state);
}

/**
 * @deprecated Use {@link Zikula.checkboxswitchdisplaystate}
 */
function checkboxswitchdisplaystate(idcheckbox, idcontainer, state)
{
    return Zikula.checkboxswitchdisplaystate(idcheckbox, idcontainer, state);
}

/**
 * Javascript implementation of PHP str_repeat function.
 *
 * @param {string} i The string to be repeated.
 * @param {int}    m Number of time the input string should be repeated.
 *
 * @return {string} The repeated string.
 * @author Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
 * @link http://www.diveintojavascript.com/projects/sprintf-for-javascript
 */
Zikula.str_repeat = function(i, m) {
    for (var o = []; m > 0; o[--m] = i);
    return o.join('');
}

/**
 * Javascript implementation of PHP sprintf function.
 *
 * @param {string} format Format string. PHP sprintf syntax is used.
 * @param {mixed}  args   Zero or more replacements to be made according to format string.
 *
 * @return {string} A string produced according to the formatting string format.
 * @author Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
 * @link http://www.diveintojavascript.com/projects/sprintf-for-javascript
 */
Zikula.sprintf = function () {
    var i = 0, a, f = arguments[i++], o = [], m, p, c, x, s = '';
    while (f) {
        if (m = /^[^\x25]+/.exec(f)) {
            o.push(m[0]);
        }
        else if (m = /^\x25{2}/.exec(f)) {
            o.push('%');
        }
        else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
            if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) {
                throw('Too few arguments.');
            }
            if (/[^s]/.test(m[7]) && (typeof(a) != 'number')) {
                throw('Expecting number but found ' + typeof(a));
            }
            switch (m[7]) {
                case 'b':a = a.toString(2);break;
                case 'c':a = String.fromCharCode(a);break;
                case 'd':a = parseInt(a);break;
                case 'e':a = m[6] ? a.toExponential(m[6]) : a.toExponential();break;
                case 'f':a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a);break;
                case 'o':a = a.toString(8);break;
                case 's':a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a);break;
                case 'u':a = Math.abs(a);break;
                case 'x':a = a.toString(16);break;
                case 'X':a = a.toString(16).toUpperCase();break;
            }
            a = (/[def]/.test(m[7]) && m[2] && a >= 0 ? '+'+ a : a);
            c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
            x = m[5] - String(a).length - s.length;
            p = m[5] ? Zikula.str_repeat(c, x) : '';
            o.push(s + (m[4] ? a + p : p + a));
        }
        else {
            throw('Huh ?!');
        }
        f = f.substring(m[0].length);
    }
    return o.join('');
}

/**
 * Javascript implementation of PHP vsprintf function.
 *
 * @param {string} format Format string. PHP sprintf syntax is used.
 * @param {array}  args   Array with zero or more replacements to be made according to format string.
 *
 * @return {string} A string produced according to the formatting string format.
 */
Zikula.vsprintf = function(format, args) {
    return Zikula.sprintf.apply(this,[format].concat(args));
}

/**
 * Merge two objects recursively.
 *
 * Copies all properties from source to destination object and returns new object.
 * If proprety exists in destination it is extended not overwritten
 *
 * @param {object} destination Destination object
 * @param {object} source      Source object
 *
 * @return {object} Extended object
 */
Zikula.mergeObjects = function(destination,source)
{
    destination = destination || {};
    for (var prop in source) {
        try {
            if (source[prop].constructor==Object ) {
                destination[prop] = Zikula.mergeObjects(destination[prop], source[prop]);
            } else {
                destination[prop] = source[prop];
            }
        } catch(e) {
            destination[prop] = source[prop];
        }
    }
    return destination;
}

Zikula.Gettext = Class.create(/** @lends Zikula.Gettext.prototype */{
    /**
     * Defaults options
     * 
     * @private
     * @type object
     */
    defaults: {
        lang: 'en',
        domain: 'zikula',
        pluralForms: 'nplurals=2; plural=n == 1 ? 0 : 1;'
    },
    /**
     * Regexp used for validating plural forms
     *
     * @private
     * @type RegExp
     */
    pluralsPattern: /^(nplurals=\d+;\s{0,}plural=[\s\d\w\(\)\?:%><=!&\|]+)\s{0,};\s{0,}$/i,
    /**
     * Null char used as delimiter for plural forms
     * 
     * @private
     * @type string
     */
    nullChar: '\u0000',
    /**
     * Javascript Gettext implementation for Zikula.
     * 
     * Base class for javascript gettext implementation. It runs internal and
     * exports utility methods to global Zikula object.
     * This are {@link Zikula#__}, {@link Zikula#__f}, {@link Zikula#_n} and {@link Zikula#_fn}.<br />
     * Usage is quite the same as PHP gettext
     * @example
     * Zikula.__('hello','module_foo');
     * Zikula.__f('hello %s',['A'],'module_foo');
     * Zikula._n('hello my friend','hello my friends',2,'module_foo');
     * Zikula._fn('hello my friend %s','hello my friends %s',2,['A','B'],'module_foo')
     *
     * @class Zikula.Gettext
     * @constructs
     * 
     * @param {string} lang Language for translations
     * @param {object} data Data with translations
     *
     * @return New gettext object
     */
    initialize: function(lang,data) {
        this.data = {};
        this.setup(lang,data);

        this.__ = this.getMessage.bind(this);
        this.__f = this.getMessageFormatted.bind(this);
        this._n = this.getPluralMessage.bind(this);
        this._fn = this.getPluralMessageFormatted.bind(this);
    },
    /**
     * Allows to re-init already initialized gettet instance
     *
     * @param {string} lang   Language for translations
     * @param {object} data   Data with translations
     * @param {string} domain Default domain to use, optional
     *
     * @return void
     */
    setup: function(lang,data,domain) {
        this.setLang(lang);
        this.setDomain(domain);
        this.addTranslations(data || {})
    },
    /**
     * Adds translations to gettext instance
     *
     * @param {object} obj Data with translations
     *
     * @return void
     */
    addTranslations: function(obj) {
        Zikula.mergeObjects(this.data,obj)
    },
    /**
     * Setup current gettext language
     *
     * @param {string} lang   Language for translations
     *
     * @return void
     */
    setLang: function(lang) {
        this.lang = lang || this.defaults.lang;
    },
    /**
     * Setup current gettext defaul domain
     *
     * @param {string} domain Default domain to use, optional
     *
     * @return void
     */
    setDomain: function(domain) {
        this.domain = domain || this.defaults.domain;
    },
    /**
     * Reads from translations data
     *
     * @private
     * @param {string} domain The domain in which given key will be searched
     * @param {string} key    Data key to search
     *
     * @return void
     */
    getData: function(domain,key) {
        domain = domain || this.domain;
        try {
            return this.data[this.lang][domain][key];
        } catch (e) {
            return {};
        }
    },
    /**
     * Gettext: translates message.
     *
     * @example
     * Zikula.__('hello','module_foo');
     * 
     * @param {string} msgid    The message to translate
     * @param {string} [domain] Gettext domain, if no domain is given deafult one is used
     *
     * @return {string} Translated message
     */
    getMessage: function(msgid, domain) {
        return this.getData(domain,'translations')[msgid] || msgid;
    },
    /**
     * Gettext: translates and format message using sprintf formatting rules.
     *
     * @example
     * Zikula.__f('hello %s',['A'],'module_foo');
     *
     * @param {string} msgid    The message to translate
     * @param {array}  params   Array with zero or more replacements to be made in msgid
     * @param {string} [domain] Gettext domain, if no domain is given deafult one is used
     *
     * @return {string} Translated message
     */
    getMessageFormatted: function(msgid, params, domain) {
        return Zikula.vsprintf(this.getMessage(msgid, domain), params);
    },
    /**
     * Gettext: tlural translation.
     *
     * @example
     * Zikula._n('hello my friend','hello my friends',2,'module_foo');
     *
     * @param {string} singular   Singular message
     * @param {string} plural     Plural message
     * @param {int}    count      Count
     * @param {string} [domain]   Gettext domain, if no domain is given deafult one is used
     *
     * @return {string} Translated message
     */
    getPluralMessage: function(singular, plural, count, domain) {
        var offset = this.getPluralOffset(count, domain),
            key = singular + this.nullChar + plural,
            messages = this.getMessage(key, domain);
        if(messages) {
            return messages.split(this.nullChar)[offset];
        } else {
            return key.split(this.nullChar)[offset];
        }
    },
    /**
     * Gettext: tlural formatted translation.
     *
     * @example
     * Zikula._fn('hello my friend %s','hello my friends %s',2,['A','B'],'module_foo')
     *
     * @param {string} singular Singular message
     * @param {string} plural   Plural message
     * @param {int}    count    Count
     * @param {array}  params   Array with zero or more replacements to be made in singular/plural message
     * @param {string} [domain] Gettext domain, if no domain is given deafult one is used
     *
     * @return {string} Translated message
     */
    getPluralMessageFormatted: function(singular, plural, count, params, domain) {
        return Zikula.vsprintf(this.getPluralMessage(singular, plural, count, domain), params);
    },
    /**
     * Calculates plural offset depending on plural forms
     *
     * @private
     * @param {int}    count  Count
     * @param {string} domain The domain to be used, if no domain is given deafult one is used
     *
     * @return {int} Plural offset
     */
    getPluralOffset: function(count, domain) {
        var eq = null,
            nplurals = 0,
            plural = 0,
            n = count || 0;
        try {
            eq = this.getData(domain,'plural-forms').match(this.pluralsPattern)[1];
            eval(eq);
        } catch(e) {
            eq = this.defaults.pluralForms;
            eval(eq);
        }
        if (plural >= nplurals) {
            plural = nplurals - 1;
        }
        return plural;
    }
});
Zikula.GettextInstance = new Zikula.Gettext(Zikula.Config.lang,Zikula._translations);
// Export shortcuts to Zikula global object.
Object.extend(Zikula,{
    __: Zikula.GettextInstance.__,
    __f: Zikula.GettextInstance.__f,
    _n: Zikula.GettextInstance._n,
    _fn: Zikula.GettextInstance._fn
});

/**
 * Util class for creating cookies.<br />
 * Cookie data is stored in JSON format so all data types valid for JSON can be stored
 * (this are string, number, object, array, true, false, null).<br />
 * Retruned data is always converted to original format. There is no need to prepare it for store.
 * 
 * @example
 * Zikula.Cookie.set('cookiename','cookievalue');
 * Zikula.Cookie.get('cookiename'); // 'cookievalue'
 * // you may also use more complicted values:
 * Zikula.Cookie.set('somedata', {arraydata: [1,2,3], bool: true, foo: 'bar'}
 * Zikula.Cookie.get('somedata'); // {arraydata: [1,2,3], bool: true, foo: 'bar'}
 * 
 * @class Zikula.Cookie
 */
Zikula.Cookie = /** @lends Zikula.Cookie */{
    /**
     * Template for cookie
     * 
     * @private
     * @type string
     */
    cookie: '#{name}=#{value};expires=#{expires};path=#{path}',
    /**
     * Create or update cookie.
     *
     * @param {string}       name     Cookie name.
     * @param {mixed}        value    Cookie value.
     * @param {number|Date} [expires] Expiration date (Date object) or time in seconds, default is session.
     * @param {string}      [path]    Path for cookie, by default Zikula baseURI is set.
     *
     * @return {bool} Returns true on success, false otherwise
     */
    set: function(name, value, expires, path){
        try {
            document.cookie = Zikula.Cookie.cookie.interpolate({
                name: name,
                value: Zikula.Cookie.encode(value),
                expires: expires instanceof Date ? expires.toGMTString() : Zikula.Cookie.secondsFromNow(expires),
                path: path ? path : Zikula.Config.baseURI
            });
        } catch (e) {
            return false;
        }
        return true;
    },
    /**
     * Get cookie value.
     * Cookie value is returned in original format as it was stored.
     *
     * @param {string} name Cookie name.
     *
     * @return {mixed} Returns cookie value or null.
     */
    get: function(name){
        var cookie = document.cookie.match(name + '=(.*?)(;|$)');
        return cookie ? Zikula.Cookie.decode(cookie[1]) : null
    },
    /**
     * Delete cookie
     *
     * @param {string} name Cookie name.
     *
     * @return {bool} Returns true on success, false otherwise
     */
    remove: function(name){
        return Zikula.Cookie.set(name,'',-1);
    },
    /**
     * Calculates date equal now plus given number of seconds
     *
     * @private
     * @param {int} seconds Number of seconds
     *
     * @return {string} Date as GMT string
     */
    secondsFromNow: function(seconds) {
        var d = new Date();
        d.setTime(d.getTime() + (seconds * 1000));
        return d.toGMTString();
    },
    /**
     * Encode given value to format safe to store in cookies.
     * Due to PHPIDS original JSON format is encoded using encodeURI
     *
     * @private
     * @param {mixed} value Value to encode
     *
     * @return {string} Encoded value
     */
    encode: function(value) {
        return encodeURI(encodeURI(Object.toJSON(value)));
    },
    /**
     * Decode given string to original format
     *
     * @private
     * @param {string} value String to decode
     *
     * @return {mixed} Decoded value
     */
    decode: function(value) {
        return decodeURI(decodeURI(value)).evalJSON(true);
    }
};

/**
 * Extensions for prototype Element.Methods
 * @class
 * @name Element.Methods
 * @see http://github.com/kangax/protolicious/blob/master/element.methods.js
 */

/**
 * Element.getContentWidth(@element) -> Number
 * returns element's "inner" width - without padding/border dimensions
 *
 * @example $(someElement).getContentWidth(); // 125
 * @extends Element.Methods
 * @param {string} element Element id
 *
 * @return {int} Element content width
 */
Element.Methods.getContentWidth = function(element) {
    return ['paddingLeft', 'paddingRight', 'borderLeftWidth', 'borderRightWidth']
        .inject(Element.getWidth(element), function(total, prop) {
            return total - parseInt(Element.getStyle(element, prop), 10);
        });
};

/**
 * Element.getContentHeight(@element) -> Number
 * returns element's "inner" height - without padding/border dimensions
 *
 * @example $(someElement).getContentHeight(); // 141
 * @extends Element.Methods
 * @param {string} element Element id
 *
 * @return {int} Element content height
 */
Element.Methods.getContentHeight = function(element) {
    return ['paddingTop', 'paddingBottom', 'borderTopWidth', 'borderBottomWidth']
        .inject(Element.getHeight(element), function(total, prop) {
            return total - parseInt(Element.getStyle(element, prop), 10);
        });
};

/**
 * Element.setWidth(@element, width) -> @element
 * sets element's width to a specified value
 * or to a value of its content width (if value was not supplied)
 *
 * @example $(someElement).setWidth(); <br />$(someOtherElement).setWidth(100);
 * @extends Element.Methods
 * @param {string} element Element id
 * @param {number} [width] Element width, default is current element content width
 *
 * @return {object} Element
 */
Element.Methods.setWidth = function(element, width) {
    return Element.setStyle(element, {
        width: (Object.isUndefined(width) ? Element.getContentWidth(element) : width) + 'px'
    });
};

/**
 * Element.setHeight(@element, height) -> @element
 * sets element's height to a specified value
 * or to a value of its content height (if value was not supplied)
 *
 * @example $(someElement).setHeight(); <br />$(someOtherElement).setHeight(68);
 * @extends Element.Methods
 * @param {string} element Element id
 * @param {number} [height] Element height, default is current element content height
 *
 * @return {object} Element
 */
Element.Methods.setHeight = function(element, height) {
    return Element.setStyle(element, {
        height: (Object.isUndefined(height) ? Element.getContentHeight(element) : height) + 'px'
    });
};

/**
 * Element.getOutlineSize(@element, type) -> Number
 * calculates element's "outline" size based on given type.
 * As "outline" properties, such as borders and margins are counted.
 * Type can be:
 * - vertical (v) - top and bottom are counted
 * - horizontal (h) - left and right are counded
 * - left, right, top, bottom
 *
 * @example $(someElement).getOutlineSize('vertical'); // 10
 * @extends Element.Methods
 * @param {string} element Element id
 * @param {string} type    Type of outline
 *
 * @return {object} The sum of the given properties
 */
Element.Methods.getOutlineSize = function(element, type) {
    type = type ? type.toLowerCase() : 'vertical';
    var props;
    switch(type) {
        case 'vertical':
        case 'v':
            props = ['borderTopWidth','borderBottomWidth','marginTop','marginBottom'];
            break;
        case 'horizontal':
        case 'h':
            props = ['borderLeftWidth','borderRightWidth','marginLeft','marginRight'];
            break;
        default:
            props = [('margin-'+type).camelize(),('border-'+type+'-Width').camelize()];
    }
    return props
        .inject(0, function(total, prop) {
            return total + parseInt(Element.getStyle(element, prop), 10);
        });
};
// Apply new methods to prototype Element
Element.addMethods();

/**
 * This will be removed.
 * @ignore
 */
Object.extend(String.prototype, (function() {
    function toUnits(unit) {
        return (parseInt(this) || 0) + (unit || 'px');
    }
    return {
        toUnits:         toUnits
    };
})());
/**
 * This will be removed.
 * @ignore
 */
Object.extend(Number.prototype, (function() {
    function toUnits(unit) {
        return (this.valueOf() || 0) + (unit || 'px');
    }
    return {
        toUnits:         toUnits
    };
})());
