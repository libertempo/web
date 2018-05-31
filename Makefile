.PHONY: install
DEFAULT: install

destroy:
	App/Tools/destroy
setup:
	App/Tools/setup ${nom_instance}
update:
	App/Tools/update

install: setup update

reinstall: destroy install
