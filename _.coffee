window.log = () ->
	log.history = log.history || []
	log.history.push arguments
	if @console
		console.log arguments

$ ->
	log "init at " + new Date