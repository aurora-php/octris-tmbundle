#
# Build Octris.tmbundle
#

SHELL:=$(shell which bash)
CURSYMDIR:=$(shell pwd)

build::
	@if [ ! -d $(CURSYMDIR)/build/Octris.tmbundle ]; then \
		mkdir -p $(CURSYMDIR)/build/Octris.tmbundle; \
	else \
		rm -rf $(CURSYMDIR)/build/Octris.tmbundle/*; \
	fi
	@( \
		cd $(CURSYMDIR)/src/Support/php; \
		composer update; \
	)
	@tar -c --exclude .git -C src . | tar -x -C $(CURSYMDIR)/build/Octris.tmbundle
