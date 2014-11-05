#
# Build Octris.tmbundle
#

SHELL:=$(shell which bash)
CURSYMDIR:=$(shell pwd)

build::
	@if [ ! -d $(CURSYMDIR)/build/Octris.tmbundle ]; then \
		mkdir -p $(CURSYMDIR)/build/Octris.tmbundle; \
	fi
	@( \
		cd $(CURSYMDIR)/src/Support/php; \
		composer update; \
	)
	@cp -R $(CURSYMDIR)/src/* $(CURSYMDIR)/build/Octris.tmbundle
