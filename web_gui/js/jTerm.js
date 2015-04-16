// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {

    "use strict";

    // Create the defaults once
    var pluginName = "jTerm",
    defaults = {
	classPrefix: '',
    };

    // The actual plugin constructor
    function Plugin ( element, options ) {
        this.element = element;
        
        // jQuery has an extend method which merges the contents of two or
        // more objects, storing the result in the first object. The first object
        // is generally empty as we don't want to alter the default options for
        // future instances of the plugin
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;
        this._name = pluginName;
	this.oContainer = $(this.element);
	this.oHistContainer = $('<div/>');
	this.oHistory = $('<ul/>');
	this.oFakeInput = $('<div/>');
	this.oCmdBar = $('<div/>');
	this.oTxt = $('<textarea/>');
	this.oBtn = $('<button/>');

        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function () {
	    var cpfx = this.settings.classPrefix;
	    this.oHistContainer.addClass(cpfx + 'hc');
	    this.oHistory.addClass(cpfx + 'hcl');
	    this.oCmdBar.addClass(cpfx + 'cmd');
	    this.oFakeInput.addClass(cpfx + 'fi');
	    this.oBtn.addClass(cpfx + 'bS');
	    this.oTxt.addClass(cpfx + 'txt');
	    this.prompt=$('<span/>');
	    this.prompt.addClass(cpfx + 'prompt');
	    this.oFakeInput.append(this.prompt);
	    this.oFakeInput.append(this.oTxt);
	    this.oCmdBar.append(this.oFakeInput);
	    
	    this.oCmdBar.append(this.oBtn);
	    this.oHistContainer.append(this.oHistory);
	    this.oContainer.append(this.oHistContainer);
	    this.oContainer.append(this.oCmdBar);
	    this.prompt.text('root@localhost:-$');

	    this.doBindings();
        },
	addLine: function(text, klass) {
	    klass = klass || '';
	    var li = $('<li/>');
	    if (klass) {
		li.addClass(klass);
	    }
	    li.text(text);
	    this.oHistory.append(li);
	},
	handleResponse: function(response) {
	    var r = response || {};
	    if (r.output) {
		this.addLine('|+|> ' + r.output, r.cssClass);
	    } else {
		this.handleResponse({
		    'output': "Command not found",
		    'cssClass': 'jt-error'
		});
	    }
	},
	serverRequest: function(cmd) {
	    console.log("Fake request for '" + cmd + "'");
	    var self = this;
	    $.ajax({
		url: 'ajax.php',
		type: 'get',
		dataType: 'json',
		data: {
		    cmd: cmd
		},
		success: function(r) {
		    self.handleResponse(r);
		},
		error: function(e) {
		    console.warn(e);
		    alert('Ajax error');
		}
	    });
	    
	    
	},
	checkCommand: function(cmdstring) {
	    if (cmdstring) {
		switch (cmdstring) {
		case 'clear':
		case 'cls':
		    this.oHistory.html('');
		    this.oTxt.val('');
		    break;
		default:
		    this.serverRequest(cmdstring);
		}
	    }
	},
	sendCommand: function(value) {
	    var value = value || this.oTxt.val() || '';
	    value = value.trim();
	    console.log({
		'command': value
	    });
	    this.addLine(this.prompt.text() + ' ' + value);
	    this.checkCommand(value);
	    this.oTxt.val('');
	},
        doBindings: function () {
	    var self = this;
	    self.oTxt.keyup(function(e) {
		switch(e.keyCode) {
		case 13:
		    self.sendCommand($(this).val());
		}
	    });
        }
    });

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[ pluginName ] = function ( options ) {
        return this.each(function() {
            if ( !$.data( this, "plugin_" + pluginName ) ) {
                $.data( this, "plugin_" + pluginName, new Plugin( this, options ) );
            }
        });
    };

})( jQuery, window, document );

$("#jTerm").jTerm();
