#!/usr/bin/env bash

git pull && git push && git checkout master && git pull && git merge wip && git push && git co wip

