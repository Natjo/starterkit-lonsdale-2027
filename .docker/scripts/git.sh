#!/bin/bash

if [ -z "$1" ]; then
  echo "Usage: $0 <new-git-url>"
  exit 1
fi

git remote set-url origin "$1"
echo "Git remote 'origin' updated to: $1"
git remote -v
