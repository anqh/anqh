#!/bin/sh
git stash
git pull --rebase
git submodule update
git stash pop
