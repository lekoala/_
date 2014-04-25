
# Ndrscr php toolkit
# ------------------------
#  
# @author Thomas Portelange <thomas@lekoala.be>
# @version 0.1
# @licence http://www.opensource.org/licenses/MIT

# export
exports = this

# log wrapper
window.log = () ->
	log.history = log.history || []
	log.history.push arguments
	if @console
		console.log arguments
		
# addParameter
updateQuerystring = (k,v) ->
	currentQuerystring = window.location.search.substring 1
	newQuerystring = ''
	first = true
	found = false
	
	if currentQuerystring.length > 0
		params = currentQuerystring.split "&"
		for param in params
			paramParts = param.split "="
					
			if first
				first = false
			else 
				newQuerystring = newQuerystring + '&'
				
			if k is paramParts[0]
				found = true
				newQuerystring = newQuerystring + paramParts[0] + '=' + v
			else
				newQuerystring = newQuerystring + param
	if not found
		if not first
			newQuerystring = newQuerystring + '&'
		newQuerystring = newQuerystring + k + '=' + v
	
	newLocation = self.location.protocol + '//' + self.location.host + self.location.pathname + '?' + newQuerystring + self.location.hash
	window.location = newLocation

# nicer looking time functions
after = (ms,cb) -> setTimeout cb, ms
every = (ms,cb) -> setInterval cb, ms

# jgrowl replacement if not available @link https://bitbucket.org/stanlemon/jgrowl/
if(!jQuery.fn.jGrowl)
	$.jGrowl = (m, o) ->
		jgrowl = $ '#jGrowl'
		if(jgrowl.length is 0)
			jgrowl = $('<div id="jGrowl" class="jGrowl"></div>')
			jgrowl.appendTo('body')
		jgrowl.jGrowl(m,o)
	$.fn.jGrowl = (m,o) ->
		defaults = 
			header : ''
			life : 3000
		o = $.extend {}, defaults, o
		log "growl : " + m
		notification = $('
		<div class="jGrowl-notification">
		<div class="jGrowl-close">x</div>
		<div class="jGrowl-header">'+o.header+'</div>
		<div class="jGrowl-message">'+m+'</div>
		</div>
		').appendTo(this).fadeIn()
		notification.find('.jGrowl-close').click (e) ->
			$(this).parents('.jGrowl-notification').fadeOut()
		after o.life, () ->
			notification.fadeOut()

# spin plugin @link http://fgnass.github.com/spin.js/
$.fn.spin = (opts) ->
	this.each () ->
		$this = $(this)
		data = $this.data()
		if data.spinner
			data.spinner.stop()
			delete data.spinner
		if opts isnt false
			opts = $.extend({color : $this.css('color')},opts)
			data.spinner = new Spinner(opts)
			data.spinner.spin(this)

# show on dom loaded
$ ->
	log "init at " + new Date