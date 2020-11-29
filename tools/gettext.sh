#!/bin/bash
find . -iname "*.phpt" -o -iname "*.php" | xargs xgettext --from-code=UTF-8 -o "src/CatLab/Accounts/locales/messages.pot" -L php --keyword=getText
