mkdir -p ~/workspace/magenta/trivex/api/event/api/utils
rm -R -f ~/workspace/magenta/trivex/api/event/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/event/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/event/api/utils/

echo 'copy Entity from event'
cp -R -p ~/workspace/magenta/trivex/api/event/api/src/Entity/* ~/workspace/magenta/trivex/api/admin/src/Entity/Event
cd ~/workspace/magenta/trivex/api/admin/src/Entity/Event
sed -i -- 's/App\\Entity/App\\Entity\\Event/g' *
sed -i -- 's/App\\Util/App\\Util\\Event/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Event/g' *

echo 'copy Repository from event'
cp -R -p ~/workspace/magenta/trivex/api/event/api/src/Repository/* ~/workspace/magenta/trivex/api/admin/src/Repository/Event
cd ~/workspace/magenta/trivex/api/admin/src/Repository/Event
sed -i -- 's/App\\Entity/App\\Entity\\Event/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Event/g' *

echo 'copy Security from event'
cp -p ~/workspace/magenta/trivex/api/event/api/src/Security/* ~/workspace/magenta/trivex/api/admin/src/Security

echo 'copy Subscriber from event'
cp -p ~/workspace/magenta/trivex/api/event/api/src/Doctrine/Subscriber/* ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
cd ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
sed -i -- 's/App\\Util;/App\\Util\\Event;/g' *
sed -i -- 's/App\\Util\\/App\\XXXUtil\\Event\\/g' *
sed -i -- 's/App\\Entity/App\\XXXEntity\\Event/g' *

echo 'copy Message from event'
rm -R -f ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Event/*
cp -R -p ~/workspace/magenta/trivex/api/event/api/src/Message/Entity/* ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Event
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Event
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Event/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Event/g' *
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Event/V1
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Event/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Event/g' *
sed -i -- 's/App\\Util/App\\Util\\Event/g' *

echo 'copy Util from event'
# libraries\component\utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/admin/libraries/component/utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src/Util/*  ~/workspace/magenta/trivex/api/admin/src/Util/Event
cp ~/workspace/magenta/trivex/api/event/api/src/Util/AppUtil.php ~/workspace/magenta/trivex/api/admin/src/Util/Event
cd ~/workspace/magenta/trivex/api/admin/src/Util/Event
sed -i -- 's/App\\Util;/App\\Util\\Event;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Event/g' *

echo 'copy Command from event'
cp -R -p ~/workspace/magenta/trivex/api/event/api/src/Command/* ~/workspace/magenta/trivex/api/admin/src/Command/Event;
rm -f ~/workspace/magenta/trivex/api/admin/src/Command/Event/AwsSqsWorkerCommand.php;
cd ~/workspace/magenta/trivex/api/admin/src/Command/Event;
sed -i -- 's/App\\Util/App\\Util\\Event/g' *
sed -i -- 's/App\\Command;/App\\Command\\Event;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Event/g' *
