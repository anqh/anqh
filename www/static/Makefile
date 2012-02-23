BOOTSTRAP = ./css/bootstrap.css
BOOTSTRAP_LESS = ./bootstrap.less
BOOTSTRAP_RESPONSIVE = ./css/bootstrap-responsive.css
BOOTSTRAP_RESPONSIVE_LESS = ./bootstrap/less/responsive.less
LESS_COMPRESSOR ?= `which lessc`
WATCHR ?= `which watchr`

#
# BUILD SIMPLE BOOTSTRAP DIRECTORY
# lessc & uglifyjs are required
#

bootstrap:
	lessc ${BOOTSTRAP_LESS} > css/bootstrap.css
	lessc --compress ${BOOTSTRAP_LESS} > css/bootstrap.min.css
	lessc ${BOOTSTRAP_RESPONSIVE_LESS} > css/bootstrap-responsive.css
	lessc --compress ${BOOTSTRAP_RESPONSIVE_LESS} > css/bootstrap-responsive.min.css
	cat \
		bootstrap/js/bootstrap-transition.js \
		bootstrap/js/bootstrap-alert.js \
		bootstrap/js/bootstrap-button.js \
		bootstrap/js/bootstrap-carousel.js \
		bootstrap/js/bootstrap-collapse.js \
		bootstrap/js/bootstrap-dropdown.js \
		bootstrap/js/bootstrap-modal.js \
		bootstrap/js/bootstrap-tooltip.js \
		bootstrap/js/bootstrap-popover.js \
		bootstrap/js/bootstrap-scrollspy.js \
		bootstrap/js/bootstrap-tab.js \
		bootstrap/js/bootstrap-typeahead.js > js/bootstrap.js
	uglifyjs -nc js/bootstrap.js > js/bootstrap.min.js

#
# WATCH LESS FILES
#

watch:
	echo "Watching less files..."; \
	watchr -e "watch('bootstrap/less/.*\.less') { system 'make' }"


.PHONY: bootstrap watch
