#!/bin/bash
CURRENT_DIR="$(pwd)"
rm ./collector.ocmod.zip
rm -rf /tmp/ocbuild
mkdir -p /tmp/ocbuild/upload
cp ./install.sql /tmp/ocbuild
cp ./install.xml /tmp/ocbuild
cp -a ../admin /tmp/ocbuild/upload
cp -a ../catalog /tmp/ocbuild/upload
cp -a ../vendors /tmp/ocbuild/upload
cd /tmp/ocbuild/
zip -r "$CURRENT_DIR/collector.ocmod.zip" ./*
cd -
