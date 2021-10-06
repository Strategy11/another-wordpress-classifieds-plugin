#!/bin/bash
for i in $( git diff --name-only -- {*,*/*}/languages/*.{po,pot,mo} 2>/dev/null ); do
    # count lines that start with '+"', but exclude:
    # - '+"POT-Creation-Date'
    # - '+"Project-Id-Version'
    # - '+"PO-Revision-Date'
    matches=$( git diff -U0 -- $i 2>/dev/null | ack '(?:^\+(?!"POT-Creation-Date)(?!"Project-Id-Version)(?!"PO-Revision-Date)(?!#)(?!\s)(?!\+))' | wc -l );

    if [ "$matches" -eq "0" ]; then
        git checkout --quiet $i;
        echo "Removing modifications from $i. There are no new strings.";
    fi;
done;
