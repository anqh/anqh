BOOTSTRAP = ./css/bootstrap.css
BOOTSTRAP_LESS = ./bootstrap.less
BOOTSTRAP_RESPONSIVE = ./css/bootstrap-responsive.css
BOOTSTRAP_RESPONSIVE_LESS = ./bootstrap-j/less/responsive.less
JASNY_BOOTSTRAP_RESPONSIVE_LESS = ./bootstrap-j/less/jasny-responsive.less
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
	@echo "Building Bootstrap..."
	@echo "${HR}"
	@jshint ./bootstrap-j/js/*.js --config ./bootstrap-j/js/.jshintrc
	@jshint ./bootstrap-j/js/tests/unit/*.js --config ./bootstrap-j/js/.jshintrc
	@echo "Running JSHint on javascript...             ${CHECK} Done"
	@recess --compile ${BOOTSTRAP_LESS} > ${BOOTSTRAP}
	@recess --compile ${JASNY_BOOTSTRAP_RESPONSIVE_LESS} ${BOOTSTRAP_RESPONSIVE_LESS} > ${BOOTSTRAP_RESPONSIVE}
	@echo "Compiling LESS with Recess...               ${CHECK} Done"
#	@node docs/build
	@cp ./bootstrap-j/img/* ${IMG}
#	@cp ./bootstrap-j/js/*.js ${JS}
	@cp -r ./bootstrap-j/font .
#	@cp js/tests/vendor/jquery.js ${JS}
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
		./bootstrap-j/js/bootstrap-affix.js > ${JS}bootstrap.js
	@uglifyjs -nc -v ${JS}bootstrap.js > ${JS}bootstrap.min.tmp.js
	@echo "/** Bootstrap.js v2.2.1-j3 by @fat & @mdo extended by @ArnoldDaniels - Copyright 2012 Twitter, Inc. http://www.apache.org/licenses/LICENSE-2.0.txt */" > ${JS}copyright.js
	@cat ${JS}copyright.js ${JS}bootstrap.min.tmp.js > ${JS}bootstrap.min.js
	@rm ${JS}copyright.js ${JS}bootstrap.min.tmp.js
	@echo "Compiling and minifying javascript...       ${CHECK} Done"
	@echo "${HR}"
	@echo "Bootstrap successfully built at ${DATE}."
	@echo "${HR}"
	@echo "Thanks for using Jasny Bootstrap,"
	@echo "<3 @mdo, @fat & @ArnoldDaniels"

#
# RUN JSHINT & QUNIT TESTS IN PHANTOMJS
#

test:
	jshint js/*.js --config js/.jshintrc
	jshint js/tests/unit/*.js --config js/.jshintrc
	node js/tests/server.js &
	phantomjs js/tests/phantom.js "http://localhost:3000/js/tests"
	kill -9 `cat js/tests/pid.txt`
	rm js/tests/pid.txt

#
# CLEANS THE ROOT DIRECTORY OF PRIOR BUILDS
#

#clean:
#	rm -r bootstrap

#
# BUILD SIMPLE BOOTSTRAP DIRECTORY
# recess & uglifyjs are required
#

#bootstrap:
#	mkdir -p bootstrap/img
#	mkdir -p bootstrap/css
#	mkdir -p bootstrap/js
#	mkdir -p bootstrap/font
#	cp img/* bootstrap/img/
#	cp font/* bootstrap/font/
#	recess --compile ${BOOTSTRAP_LESS} ${JASNY_BOOTSTRAP_LESS} > bootstrap/css/bootstrap.css
#	recess --compress ${BOOTSTRAP_LESS} ${JASNY_BOOTSTRAP_LESS} > bootstrap/css/bootstrap.min.css
#	recess --compile ${BOOTSTRAP_RESPONSIVE_LESS} ${JASNY_BOOTSTRAP_RESPONSIVE_LESS} > bootstrap/css/bootstrap-responsive.css
#	recess --compress ${BOOTSTRAP_RESPONSIVE_LESS} ${JASNY_BOOTSTRAP_RESPONSIVE_LESS} > bootstrap/css/bootstrap-responsive.min.css
#	cat js/bootstrap-transition.js js/bootstrap-alert.js js/bootstrap-button.js js/bootstrap-carousel.js js/bootstrap-collapse.js js/bootstrap-dropdown.js js/bootstrap-modal.js js/bootstrap-tooltip.js js/bootstrap-popover.js js/bootstrap-scrollspy.js js/bootstrap-tab.js js/bootstrap-typeahead.js js/bootstrap-inputmask.js js/bootstrap-rowlink.js js/bootstrap-fileupload.js js/bootstrap-affix.js > bootstrap/js/bootstrap.js
#	uglifyjs -nc bootstrap/js/bootstrap.js > bootstrap/js/bootstrap.min.tmp.js
#	echo "/*!\n* Bootstrap.js v2.1.1-j2 by @fat & @mdo extended by @ArnoldDaniels\n* Copyright 2012 Twitter, Inc.\n* http://www.apache.org/licenses/LICENSE-2.0.txt\n*/" > bootstrap/js/copyright.js
#	cat bootstrap/js/copyright.js bootstrap/js/bootstrap.min.tmp.js > bootstrap/js/bootstrap.min.js
#	rm bootstrap/js/copyright.js bootstrap/js/bootstrap.min.tmp.js

#
# BUILD SIMPLE JASNY-BOOTSTRAP DIRECTORY
# recess & uglifyjs are required
#

jasny-bootstrap:
	mkdir -p jasny-bootstrap/css
	mkdir -p jasny-bootstrap/js
	mkdir -p jasny-bootstrap/font
	cp js/bootstrap-inputmask.js js/bootstrap-rowlink.js js/bootstrap-fileupload.js jasny-bootstrap/js
	cp font/* jasny-bootstrap/font/
	recess --compile ${JASNY_BOOTSTRAP_LESS} > jasny-bootstrap/css/jasny-bootstrap.css
	recess --compress ${JASNY_BOOTSTRAP_LESS} > jasny-bootstrap/css/jasny-bootstrap.min.css
	recess --compile ${JASNY_BOOTSTRAP_RESPONSIVE_LESS} > jasny-bootstrap/css/jasny-bootstrap-responsive.css
	recess --compress ${JASNY_BOOTSTRAP_RESPONSIVE_LESS} > jasny-bootstrap/css/jasny-bootstrap-responsive.min.css
	cat js/bootstrap-inputmask.js js/bootstrap-rowlink.js js/bootstrap-fileupload.js > jasny-bootstrap/js/jasny-bootstrap.js
	uglifyjs -nc jasny-bootstrap/js/jasny-bootstrap.js > jasny-bootstrap/js/jasny-bootstrap.min.tmp.js
	echo "/*!\n* Jasny-Bootstrap.js j2 by @ArnoldDaniels\n* Copyright 2012 Jasny BV.\n* http://www.apache.org/licenses/LICENSE-2.0.txt\n*/" > jasny-bootstrap/js/copyright.js
	cat jasny-bootstrap/js/copyright.js jasny-bootstrap/js/jasny-bootstrap.min.tmp.js > jasny-bootstrap/js/jasny-bootstrap.min.js
	rm jasny-bootstrap/js/copyright.js jasny-bootstrap/js/jasny-bootstrap.min.tmp.js

#
# WATCH LESS FILES
#

watch:
	echo "Watching less files..."; \
	watchr -e "watch('.*\.less') { system 'make' }"

.PHONY: docs watch
