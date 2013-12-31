ANQH = ./css/anqh.css
ANQH_LESS = ./anqh.less
IMG = img/
JS = js/
FONT = css/
DATE=$(shell date +%I:%M%p)
CHECK=✔
HR=\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#


#
# BUILD DOCS
#

build:
	@echo "${HR}"
	@echo "Building static files..."
	@echo "${HR}"

	@jshint ./bootstrap-j/js/*.js --config ./bootstrap-j/js/.jshintrc
	@jshint ./bootstrap-j/js/tests/unit/*.js --config ./bootstrap-j/js/.jshintrc
	@echo "Running JSHint on javascript...             ${CHECK} Done"

	@recess --compress ${ANQH_LESS} > ${ANQH}
	@echo "Compiling LESS with Recess...               ${CHECK} Done"

	@cp ./bootstrap-j/img/* ${IMG}
	@cp -r ./bootstrap-j/font .
#	@echo "Compiling documentation...                  ${CHECK} Done"

	@cat \
		./bootstrap-j/js/bootstrap-transition.js \
		./bootstrap-j/js/bootstrap-alert.js \
		./bootstrap-j/js/bootstrap-button.js \
		./bootstrap-j/js/bootstrap-carousel.js \
		./bootstrap-j/js/bootstrap-collapse.js \
		./bootstrap-j/js/bootstrap-dropdown.js \
		./bootstrap-j/js/bootstrap-modal.js \
		./bootstrap-j/js/bootstrap-tooltip.js \
		./bootstrap-j/js/bootstrap-popover.js \
		./bootstrap-j/js/bootstrap-scrollspy.js \
		./bootstrap-j/js/bootstrap-tab.js \
		./bootstrap-j/js/bootstrap-typeahead.js \
		./bootstrap-j/js/bootstrap-inputmask.js \
		./bootstrap-j/js/bootstrap-rowlink.js \
		./bootstrap-j/js/bootstrap-fileupload.js \
		./bootstrap-j/js/bootstrap-affix.js \
		${JS}vendor/jquery.markitup.js \
		${JS}vendor/markitup.bbcode.js \
		${JS}vendor/jquery.tools.min.js \
		${JS}vendor/jquery.imgareaselect.js \
		${JS}vendor/jquery-scrolltofixed.js \
		${JS}vendor/jquery.lazyload.js > ${JS}c/vendor.js
	@uglifyjs -nc -v ${JS}c/vendor.js > ${JS}c/vendor.min.js
	@cat \
		${JS}anqh/anqh.js \
		${JS}anqh/jquery.form.js \
		${JS}anqh/jquery.googlemap.js > ${JS}/c/anqh.js
	@uglifyjs -nc -v ${JS}c/anqh.js > ${JS}c/anqh.min.js
	@echo "Compiling and minifying javascript...       ${CHECK} Done"

#
# WATCH LESS FILES
#

watch:
	echo "Watching less files..."; \
	watchr -e "watch('.*\.less') { system 'make' }"

.PHONY: watch
