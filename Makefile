.PHONY: install
DEFAULT: install

install:
	@App/Tools/install ${NOM_INSTANCE}
	@App/Tools/update

update:
	@App/Tools/update
