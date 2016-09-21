#!/bin/bash
WORKING_DIR=$(dirname $0)
PLUGIN_NAME=$(basename ${WORKING_DIR})
VERSION=$(php -r "echo preg_replace('/.*<version>([^<]+)<\/version>.*/ims', '\\1', file_get_contents('${WORKING_DIR}/plugin.xml'), 1);")
TEMP_DIR='/tmp/ShopwarePlugins/'${PLUGIN_NAME}
CURRENT_DIR=$(pwd)
EXCLUDES="sftp-config.json nbproject .idea composer.json composer.lock vendor/autoload.php vendor/composer"

# Remove existing package file
if [ -f "${CURRENT_DIR}/${PLUGIN_NAME}-${VERSION}.zip" ]; then
    rm "${CURRENT_DIR}/${PLUGIN_NAME}-${VERSION}.zip"
fi

# Create temporary build folder
mkdir -p ${TEMP_DIR}

# Copy all files from source folder to temporary folder
cp -Rp ${WORKING_DIR}/* ${TEMP_DIR}

# Remove build.sh from temporary folder
rm "${TEMP_DIR}/build.sh"

# Remove excludes from temporary folder
for i in ${EXCLUDES}; do
    if [ -e "${TEMP_DIR}/$i" ]; then
        rm -R "${TEMP_DIR}/$i"
    fi
done

# Step into the temporary build folder
cd ${TEMP_DIR}

# Move one level up in folder structure
cd ..

# Zip temporary folder contents to package file in plugin folder
zip -qr "${CURRENT_DIR}/${PLUGIN_NAME}-${VERSION}.zip" $(basename ${TEMP_DIR})

# Step back into the current plugin folder
cd ${CURRENT_DIR}

echo "Package wurde erstellt unter ${CURRENT_DIR}/${PLUGIN_NAME}-${VERSION}.zip"

# Remove temporary build folder
rm -R $(dirname ${TEMP_DIR})
