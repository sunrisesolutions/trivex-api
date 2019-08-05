mkdir -p ~/workspace/magenta/trivex/api/user/api/utils
rm -R -f ~/workspace/magenta/trivex/api/user/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/user/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/user/api/utils/
echo 'copy Entity from user'
cp -R -p ~/workspace/magenta/trivex/api/user/api/src/Entity/* ~/workspace/magenta/trivex/api/admin/src/Entity/User
cd ~/workspace/magenta/trivex/api/admin/src/Entity/User
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *
sed -i -- 's/App\\Util/App\\Util\\User/g' *
sed -i -- 's/App\\Repository/App\\Repository\\User/g' *

echo 'copy Repository from user'
cp -R -p ~/workspace/magenta/trivex/api/user/api/src/Repository/* ~/workspace/magenta/trivex/api/admin/src/Repository/User
cd ~/workspace/magenta/trivex/api/admin/src/Repository/User
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *
sed -i -- 's/App\\Repository/App\\Repository\\User/g' *

echo 'copy Security from user'
cp -p ~/workspace/magenta/trivex/api/user/api/src/Security/* ~/workspace/magenta/trivex/api/admin/src/Security

echo 'copy Subscriber from user'
cp -p ~/workspace/magenta/trivex/api/user/api/src/Doctrine/Subscriber/* ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
cd ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
sed -i -- 's/App\\Util;/App\\Util\\User;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *

echo 'copy Message from user'
rm -R -f ~/workspace/magenta/trivex/api/admin/src/Message/Entity/User/*
cp -R -p ~/workspace/magenta/trivex/api/user/api/src/Message/Entity/* ~/workspace/magenta/trivex/api/admin/src/Message/Entity/User
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/User
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\User/g' *
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/User/V1
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\User/g' *
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *
sed -i -- 's/App\\Util/App\\Util\\User/g' *

echo 'copy Util from user'
cp -p ~/workspace/magenta/trivex/api/user/api/src/Util/User/* ~/workspace/magenta/trivex/api/admin/src/Util/User
# libraries\component\utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/admin/libraries/component/utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src/Util/*  ~/workspace/magenta/trivex/api/admin/src/Util/User
cp ~/workspace/magenta/trivex/api/user/api/src/Util/AppUtil.php ~/workspace/magenta/trivex/api/admin/src/Util/User
cd ~/workspace/magenta/trivex/api/admin/src/Util/User
sed -i -- 's/App\\Util;/App\\Util\\User;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *

echo 'copy Command from user'
cp -R -p ~/workspace/magenta/trivex/api/user/api/src/Command/* ~/workspace/magenta/trivex/api/admin/src/Command/User;
rm -f ~/workspace/magenta/trivex/api/admin/src/Command/User/AwsSqsWorkerCommand.php;
cd ~/workspace/magenta/trivex/api/admin/src/Command/User;
sed -i -- 's/App\\Util;/App\\Util\\User;/g' *
sed -i -- 's/App\\Command;/App\\Command\\User;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *
