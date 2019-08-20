mkdir -p ~/workspace/magenta/trivex/api/messaging/api/utils
rm -R -f ~/workspace/magenta/trivex/api/messaging/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/messaging/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/messaging/api/utils/

echo 'copy Entity from messaging'
cp -R -p ~/workspace/magenta/trivex/api/messaging/api/src/Entity/* ~/workspace/magenta/trivex/api/admin/src/Entity/Messaging
cd ~/workspace/magenta/trivex/api/admin/src/Entity/Messaging
sed -i -- 's/App\\Entity/App\\Entity\\Messaging/g' *
sed -i -- 's/App\\Util/App\\Util\\Messaging/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Messaging/g' *

echo 'copy Repository from messaging'
cp -R -p ~/workspace/magenta/trivex/api/messaging/api/src/Repository/* ~/workspace/magenta/trivex/api/admin/src/Repository/Messaging
cd ~/workspace/magenta/trivex/api/admin/src/Repository/Messaging
sed -i -- 's/App\\Entity/App\\Entity\\Messaging/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Messaging/g' *

echo 'copy Filter from messaging'
cp -R -p ~/workspace/magenta/trivex/api/messaging/api/src/Filter/* ~/workspace/magenta/trivex/api/admin/src/Filter/Messaging
cd ~/workspace/magenta/trivex/api/admin/src/Filter/Messaging
sed -i -- 's/App\\Filter/App\\Filter\\Messaging/g' *
cd ~/workspace/magenta/trivex/api/admin/src/Entity/Messaging
sed -i -- 's/App\\Filter/App\\Filter\\Messaging/g' *

echo 'copy Security from messaging'
cp -p ~/workspace/magenta/trivex/api/messaging/api/src/Security/* ~/workspace/magenta/trivex/api/admin/src/Security

echo 'copy Subscriber from messaging'
cp -p ~/workspace/magenta/trivex/api/messaging/api/src/Doctrine/Subscriber/* ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
cd ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
sed -i -- 's/App\\Util;/App\\Util\\Messaging;/g' *
sed -i -- 's/App\\Util\\/App\\XXXUtil\\Messaging\\/g' *
sed -i -- 's/App\\Entity/App\\XXXEntity\\Messaging/g' *

echo 'copy Message from messaging'
rm -R -f ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Messaging/*
cp -R -p ~/workspace/magenta/trivex/api/messaging/api/src/Message/Entity/* ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Messaging
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Messaging
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Messaging/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Messaging/g' *
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Messaging/V1
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Messaging/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Messaging/g' *
sed -i -- 's/App\\Util/App\\Util\\Messaging/g' *

echo 'copy Util from messaging'
# libraries\component\utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/admin/libraries/component/utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src/Util/*  ~/workspace/magenta/trivex/api/admin/src/Util/Messaging
cp ~/workspace/magenta/trivex/api/messaging/api/src/Util/AppUtil.php ~/workspace/magenta/trivex/api/admin/src/Util/Messaging
cd ~/workspace/magenta/trivex/api/admin/src/Util/Messaging
sed -i -- 's/App\\Util;/App\\Util\\Messaging;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Messaging/g' *

echo 'copy Command from messaging'
cp -R -p ~/workspace/magenta/trivex/api/messaging/api/src/Command/* ~/workspace/magenta/trivex/api/admin/src/Command/Messaging;
rm -f ~/workspace/magenta/trivex/api/admin/src/Command/Messaging/AwsSqsWorkerCommand.php;
rm -f ~/workspace/magenta/trivex/api/admin/src/Command/Messaging/SendMessageWorkerCommand.php;
cd ~/workspace/magenta/trivex/api/admin/src/Command/Messaging;
sed -i -- 's/App\\Util/App\\Util\\Messaging/g' *
sed -i -- 's/App\\Command;/App\\Command\\Messaging;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Messaging/g' *
