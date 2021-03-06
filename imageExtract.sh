cd "$1var/import/image"
unzip images.zip -d .
rm -f images.zip
mv * img
folder=$(find . -mindepth 1 -maxdepth 1 -type d)
find $folder/ -name '* *' -type f -exec rename ' ' '-' {} \;
find $folder/ -name '*.jpg' -type f -exec mv {} . \;
rm -rf $folder
chmod -R 777 ../image