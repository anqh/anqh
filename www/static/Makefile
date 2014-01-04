IMG   = img/
JS    = js/
CSS   = css/
DATE  = $(shell date +%I:%M%p)
CHECK = ✔
HR    = \#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#


#
# BUILD DOCS
#

build:
	@echo "${HR}"
	@echo "Building static files..."
	@echo "${HR}"

#	@jshint ./bootstrap-j/js/*.js --config ./bootstrap-j/js/.jshintrc
#	@jshint ./bootstrap-j/js/tests/unit/*.js --config ./bootstrap-j/js/.jshintrc
#	@echo "Running JSHint on javascript...             ${CHECK} Done"

	@recess --compress ${CSS}anqh.less > ${CSS}anqh.css
	@echo "Compiling LESS with Recess...               ${CHECK} Done"

#	@cp ./bootstrap-j/img/* ${IMG}
#	@cp -r ./bootstrap-j/font .
#	@echo "Compiling documentation...                  ${CHECK} Done"

	@cat \
		${JS}vendor/jquery.lazyload.js > ${JS}c/vendor.js
#		${JS}vendor/jquery.markitup.js \
#		${JS}vendor/markitup.bbcode.js \
#		${JS}vendor/jquery.imgareaselect.js
	@uglifyjs -nc -v ${JS}c/vendor.js > ${JS}c/vendor.min.js
	@cat \
		${JS}anqh/anqh.js > ${JS}c/anqh.js
#		${JS}anqh/jquery.form.js \
#		${JS}anqh/jquery.googlemap.js \
#		${JS}anqh/jquery.dialogify.js \
#		${JS}anqh/jquery.ajaxify.js \
#		${JS}anqh/jquery.autocomplete.event.js \
#		${JS}anqh/jquery.autocomplete.geo.js \
#		${JS}anqh/jquery.autocomplete.user.js \
#		${JS}anqh/jquery.autocomplete.venue.js \
#		${JS}anqh/jquery.notes.js
	@uglifyjs -nc -v ${JS}c/anqh.js > ${JS}c/anqh.min.js
	@echo "Compiling and minifying javascript...       ${CHECK} Done"

#
# WATCH LESS FILES
#

watch:
	echo "Watching less files..."; \
	watchr -e "watch('.*\.less') { system 'make' }"

.PHONY: watch
