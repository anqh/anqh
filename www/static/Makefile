JS = js/
CSS = css/
DATE = $(shell date +%I:%M%p)
CHECK = ✔


#
# BUILD DOCS
#

build:
	@echo "Building static files..."

	@lessc --compress ${CSS}anqh.less > ${CSS}anqh.css
	@echo "Compiling LESS with LESSC...               ${CHECK} Done"

	@cat \
		${JS}vendor/jquery.markitup.js \
		${JS}vendor/markitup.bbcode.js \
		${JS}vendor/jquery.imgareaselect.js \
		${JS}vendor/jquery.lazyload.js > ${JS}c/vendor.js
	@uglifyjs -nc -v ${JS}c/vendor.js > ${JS}c/vendor.min.js
	@cat \
		${JS}anqh/anqh.js \
		${JS}anqh/jquery.form.js \
		${JS}anqh/jquery.googlemap.js \
		${JS}anqh/jquery.dialogify.js \
		${JS}anqh/jquery.ajaxify.js \
		${JS}anqh/jquery.autocomplete.event.js \
		${JS}anqh/jquery.autocomplete.geo.js \
		${JS}anqh/jquery.autocomplete.user.js \
		${JS}anqh/jquery.autocomplete.venue.js \
		${JS}anqh/jquery.notes.js > ${JS}/c/anqh.js
	@uglifyjs -nc -v ${JS}c/anqh.js > ${JS}c/anqh.min.js
	@echo "Compiling and minifying javascript...       ${CHECK} Done"

#
# WATCH LESS FILES
#

watch:
	echo "Watching less files..."; \
	watchr -e "watch('.*\.less') { system 'make' }"

.PHONY: watch
