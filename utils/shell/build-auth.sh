mkdir -p ~/workspace/magenta/trivex/api/authorisation/api/utils
rm -R -f ~/workspace/magenta/trivex/api/authorisation/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/authorisation/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/authorisation/api/utils/

echo 'copy Entity from authorisation'
cp -R -p ~/workspace/magenta/trivex/api/authorisation/api/src/Entity/* ~/workspace/magenta/trivex/api/admin/src/Entity/Authorisation
cd ~/workspace/magenta/trivex/api/admin/src/Entity/Authorisation
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *
sed -i -- 's/App\\Util/App\\Util\\Authorisation/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Authorisation/g' *

echo 'copy Repository from authorisation'
cp -R -p ~/workspace/magenta/trivex/api/authorisation/api/src/Repository/* ~/workspace/magenta/trivex/api/admin/src/Repository/Authorisation
cd ~/workspace/magenta/trivex/api/admin/src/Repository/Authorisation
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Authorisation/g' *

echo 'copy Security from authorisation'
cp -p ~/workspace/magenta/trivex/api/authorisation/api/src/Security/* ~/workspace/magenta/trivex/api/admin/src/Security

echo 'copy Subscriber from authorisation'
cp -p ~/workspace/magenta/trivex/api/authorisation/api/src/Doctrine/Subscriber/* ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
cd ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
sed -i -- 's/App\\Util;/App\\Util\\Authorisation;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *

echo 'copy Message from authorisation'
rm -R -f ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Authorisation/*
cp -R -p ~/workspace/magenta/trivex/api/authorisation/api/src/Message/Entity/* ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Authorisation
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Authorisation
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Authorisation/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Authorisation/V1
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Authorisation/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *
sed -i -- 's/App\\Util/App\\Util\\Authorisation/g' *

echo 'copy Util from authorisation'
# libraries\component\utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/admin/libraries/component/utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src/Util/*  ~/workspace/magenta/trivex/api/admin/src/Util/Authorisation
cp ~/workspace/magenta/trivex/api/authorisation/api/src/Util/AppUtil.php ~/workspace/magenta/trivex/api/admin/src/Util/Authorisation
cd ~/workspace/magenta/trivex/api/admin/src/Util/Authorisation
sed -i -- 's/App\\Util;/App\\Util\\Authorisation;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *

echo 'copy Command from authorisation'
cp -R -p ~/workspace/magenta/trivex/api/authorisation/api/src/Command/* ~/workspace/magenta/trivex/api/admin/src/Command/Authorisation;
rm -f ~/workspace/magenta/trivex/api/admin/src/Command/Authorisation/AwsSqsWorkerCommand.php;
cd ~/workspace/magenta/trivex/api/admin/src/Command/Authorisation;
sed -i -- 's/App\\Util;/App\\Util\\Authorisation;/g' *
sed -i -- 's/App\\Command;/App\\Command\\Authorisation;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *
