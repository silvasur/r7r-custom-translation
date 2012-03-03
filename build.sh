#!/usr/bin/env bash

rm -f tpls/*~

r7r-plugin-packer --output=custom_translation.rpk --codefile=plugin.php --classname=custom_translation --pluginname=custom_translation --author='The Ratatöskr Team' --versiontext="0.5" --versioncount=3 --api=5 --shortdesc="Your own translations, so you can easilly internationalize your templates!" --helpfile=help.html --licensefile=COPYING --tpldir=tpls
