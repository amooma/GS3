



OLD:

.po Datei erstellen:

cd /opt/gemeinschaft/htdocs/gui/
xgettext --default-domain=gemeinschaft-gui -k__ -F --output-dir=/opt/gemeinschaft/locale/en_US/LC_MESSAGES/ `find . -name '*.php'`

