#!/bin/bash
find . -iname "*.phpt" -o -iname "*.php" | xargs xgettext --from-code=UTF-8 -o "messages.pot" -L php
