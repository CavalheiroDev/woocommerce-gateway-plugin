#!/usr/bin/env bash

BIN_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)
BASE_DIR=$BIN_DIR/..
TMP_DIR="/tmp/woocommerce-nixpay"
ZIP_FILE="woocommerce-nixpay.zip"

shopt -s extglob

if [ -d "$TMP_DIR" ]; then
	rm -rf $TMP_DIR
fi

if [ ! -d "$TMP_DIR" ]; then
	mkdir $TMP_DIR
fi

if [ -d "$ZIP_FILE" ]; then
  rm $ZIP_FILE;
fi

npm run build

cd $BASE_DIR
cp -r assets build src woocommerce-nixpay.php composer.json composer.lock $TMP_DIR

if [ $? -ne 0 ]; then
	echo "Error copying files"
	exit 1
fi

cd $TMP_DIR/ && composer install --no-dev && composer dump-autoload -o && rm composer.*

cd $TMP_DIR/.. && zip -rX woocommerce-nixpay.zip woocommerce-nixpay -x "**/.DS_Store" -x "*/.git/*"
mv $TMP_DIR/../woocommerce-nixpay.zip $BASE_DIR && rm -rf $TMP_DIR

echo "Package created successfully"
