cd "$1var/import/image"
unzip images.zip -d .
rm -f images.zip
folder=$(find . -mindepth 1 -maxdepth 1 -type d)
find $folder/ -name '* *' -type f | rename 's/ /-/g';
find $folder/ -name '*.jpg' -type f -exec mv {} . \;
rm -rf $folder
chmod -R 777 ../image