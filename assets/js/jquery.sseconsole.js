/* ========================================================================
 * SseConsole: jquery.sseconsole.js v1.0.0
 * ========================================================================
 * Copyright 2015 UnnamedTeam.
 * Licensed MIT
 * ======================================================================== */

// SSEConsole OBJECT
// ======================
var SSEConsole = {
	$element: null,
	$status: null,
	$bundle: null,
	$body: null,
	$progress: null,
	_benchmark: null,
	bodyAutoScroll: true,
	bundles: {
		info: {
			label: 'Info',
			num: 0,
		},
		warning: {
			label: 'Warning',
			num: 0,
		},
		success: {
			label: 'Success',
			num: 0,
		},
		danger: {
			label: 'Error',
			num: 0,
		},
		default: {
			label: 'All',
			num: 0,
		}
	},
	progressBars: {},
	$activeBar: null,
	maxMessageHeight: '150px',
  	construct: function(element, options){
  		this.$element = $(element);
  		this.$status = $(options.status);
  		this.$bundle = $(options.bundle);
  		this.$body = $(options.body);
  		this.$progress = $(options.progress);
  		this.bundles =  options.bundles || this.bundles;
  		this.maxMessageHeight = options.maxMessageHeight || this.maxMessageHeight;
  		this._createBundles();
  		return this;
  	},
  	_createBundles: function(){
  		var object = this;
  		bundles = object.bundles
  		for(var i in bundles){
  			code = '<span class="label label-'+i+'"  data-context="'+i+'">'+bundles[i].label+' (<span class="num">'+bundles[i].num+'</span>)</span> ';
  			object.$bundle.append(code);
  		};
  		$('.label', object.$bundle).on('click', function(){
  			object._toogleContext($(this).attr('data-context'));
  			return true;
  		});
  		object._toogleContext('default');
  		return object;
  	},
  	_addBundle : function(context){
  		bundles = this.bundles;
		$('.label[data-context='+context+'] .num', this.$bundle).html(++bundles[context].num);
		$('.label[data-context=default] .num', this.$bundle).html(++bundles.default.num);
		return this;
	},
  	getBundles: function(){
  		var string = '';
  		for(var i in arguments){
  			string +=  this.bundles[arguments[i]].label+':'+this.bundles[arguments[i]].num+'; ';
  		}
  		return string;
	},
	_cleanBundles: function(){
		for(var i in this.bundles){
			if(i == 'default')
				this.bundles[i].num = -2;
			else
	  			this.bundles[i].num = -1;
  			this._addBundle(i);
  		};
	},
	_toogleContext: function(context){
	    var style ='';
	    if(context != 'default'){
            style = '.console-body .alert-'+context+'{'+
                'max-height:'+this.maxMessageHeight+';opacity:1;'+
            '}';
	    }else{
	    	for(var i in this.bundles){
	  			style += '.console-body .alert-'+i+'{'+
	                'max-height:'+this.maxMessageHeight+';opacity:1;'+
	            '}';
	  		};
	    }
	    $('.bundle-styler').html('<style>'+style+'</style>');
	},
	setStatus: function(string){
		this.$status.html(string);
		return this;
	},
	clearStatus: function(){
		this.$status.html('-');
		return this;
	},
	addProgressBar: function(id, context, isAdditional, before){
		html = '<div id="'+id+'" class="progress-bar" style="width: 0%">0%</div>';
		if(before != undefined)
			this.progressBars[before].before(html);
		else
			this.$progress.append(html);
		bar = $('#'+id).addClass('progress-bar-'+context);
		if(isAdditional)
			bar.addClass('progress-parse');
		this.progressBars[id] = bar;
		this.toggleBar(id);
		return this;
	},
	toggleBar: function(bar){
		if(this.$activeBar !== bar)
			this.$activeBar = this.progressBars[bar];
		return this;
	},
	setProgress: function(value){
		this.$activeBar.html(value+'%').css('width',value+'%');
		return this;
	},
	deleteProgressBar: function(bar){
		if(undefined !== this.progressBars[bar]){
			this.progressBars[bar].remove();
			delete this.progressBars[bar];
		}
		return this;
	},
	startBenchmark: function(){
		this._benchmark = new Date();
		return this._benchmark;
	},
	stopBenchmark: function(){
		processingTime = new Date() - this._benchmark + new Date().getTimezoneOffset() * 60000; 
		return this._benchmark = new Date(processingTime);
	},
	addMessage: function(title, text, context){
		if(this.bundles[context]){
			var date = new Date();
			string = '<div class="alert alert-'+context+'">'+
	                +date.getHoursTwoDigits()+':'+date.getMinutesTwoDigits()+':'+date.getSecondsTwoDigits()+' <i class="fa fa-angle-double-right"></i> <strong>'+title+'</strong> '+text+
	            '</div>';
			this.$body.append(string);
			this._addBundle(context);
			if(this.bodyAutoScroll)
				this.$body.scrollTop(this.$body[0].scrollHeight);	
		}
		return this;
	},
	clearBody: function(){
		this.$body.html('');
		this._cleanBundles();
		return this;
	}
};