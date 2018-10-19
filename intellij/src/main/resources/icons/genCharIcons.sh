#!/bin/bash

php=${1:-php}

cd $(dirname "$0")
"$php" char.php group g 255 212 67
"$php" char.php message m 146 227 159
"$php" char.php arg \$ 192 152 227
