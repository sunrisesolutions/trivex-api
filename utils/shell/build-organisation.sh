mkdir -p ~/workspace/magenta/trivex/api/organisation/api/utils
rm -R -f ~/workspace/magenta/trivex/api/organisation/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/organisation/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/organisation/api/utils/

echo 'copy Entity from organisation'
cp -R -p ~/workspace/magenta/trivex/api/organisation/api/src/Entity/* ~/workspace/magenta/trivex/api/admin/src/Entity/Organisation
cd ~/workspace/magenta/trivex/api/admin/src/Entity/Organisation
sed -i -- 's/App\\Entity/App\\Entity\\Organisation/g' *
sed -i -- 's/App\\Util/App\\Util\\Organisation/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Organisation/g' *

echo 'copy Repository from organisation'
cp -R -p ~/workspace/magenta/trivex/api/organisation/api/src/Repository/* ~/workspace/magenta/trivex/api/admin/src/Repository/Organisation
cd ~/workspace/magenta/trivex/api/admin/src/Repository/Organisation
sed -i -- 's/App\\Entity/App\\Entity\\Organisation/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Organisation/g' *

echo 'copy Filter from organisation'
cp -R -p ~/workspace/magenta/trivex/api/organisation/api/src/Filter/* ~/workspace/magenta/trivex/api/admin/src/Filter/Organisation
cd ~/workspace/magenta/trivex/api/admin/src/Filter/Organisation
sed -i -- 's/App\\Filter/App\\Filter\\Organisation/g' *
cd ~/workspace/magenta/trivex/api/admin/src/Entity/Organisation
sed -i -- 's/App\\Filter/App\\Filter\\Organisation/g' *

echo 'copy Security from organisation'
cp -p ~/workspace/magenta/trivex/api/organisation/api/src/Security/* ~/workspace/magenta/trivex/api/admin/src/Security

echo 'copy Subscriber from organisation'
cp -p ~/workspace/magenta/trivex/api/organisation/api/src/Doctrine/Subscriber/* ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
cd ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
sed -i -- 's/App\\Util;/App\\Util\\Organisation;/g' *
sed -i -- 's/App\\Util\\/App\\XXXUtil\\Organisation\\/g' *
sed -i -- 's/App\\Entity/App\\XXXEntity\\Organisation/g' *

echo 'copy Message from organisation'
rm -R -f ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Organisation/*
cp -R -p ~/workspace/magenta/trivex/api/organisation/api/src/Message/Entity/* ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Organisation
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Organisation
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Organisation/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Organisation/g' *
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Organisation/V1
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Organisation/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Organisation/g' *
sed -i -- 's/App\\Util/App\\Util\\Organisation/g' *

echo 'copy Util from organisation'
# libraries\component\utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/admin/libraries/component/utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src/Util/*  ~/workspace/magenta/trivex/api/admin/src/Util/Organisation
cp ~/workspace/magenta/trivex/api/organisation/api/src/Util/AppUtil.php ~/workspace/magenta/trivex/api/admin/src/Util/Organisation
cd ~/workspace/magenta/trivex/api/admin/src/Util/Organisation
sed -i -- 's/App\\Util;/App\\Util\\Organisation;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Organisation/g' *

echo 'copy Command from organisation'
cp -R -p ~/workspace/magenta/trivex/api/organisation/api/src/Command/* ~/workspace/magenta/trivex/api/admin/src/Command/Organisation;
rm -f ~/workspace/magenta/trivex/api/admin/src/Command/Organisation/AwsSqsWorkerCommand.php;
cd ~/workspace/magenta/trivex/api/admin/src/Command/Organisation;
sed -i -- 's/App\\Util/App\\Util\\Organisation/g' *
sed -i -- 's/App\\Command;/App\\Command\\Organisation;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Organisation/g' *
