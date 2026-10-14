#!/bin/sh
# Same as composer-patches' GitInitPatcher, except that only src/ is applied:
# dist packages ship without tests/ and psalm-baseline.xml.

package_dir=$1
depth=$2
patch=$3

git -C "$package_dir" init -q \
&& git -C "$package_dir" apply -p"$depth" --include='src/*' "$patch"

status=$?

rm -rf "$package_dir/.git"

exit $status
