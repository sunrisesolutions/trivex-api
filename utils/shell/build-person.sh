mkdir -p ~/workspace/magenta/trivex/api/person/api/utils
rm -R -f ~/workspace/magenta/trivex/api/person/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/person/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/person/api/utils/

echo 'copy Entity from person'
cp -R -p ~/workspace/magenta/trivex/api/person/api/src/Entity/* ~/workspace/magenta/trivex/api/admin/src/Entity/Person
cd ~/workspace/magenta/trivex/api/admin/src/Entity/Person
sed -i -- 's/App\\Entity/App\\Entity\\Person/g' *
sed -i -- 's/App\\Util/App\\Util\\Person/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Person/g' *

echo 'copy Repository from person'
cp -R -p ~/workspace/magenta/trivex/api/person/api/src/Repository/* ~/workspace/magenta/trivex/api/admin/src/Repository/Person
cd ~/workspace/magenta/trivex/api/admin/src/Repository/Person
sed -i -- 's/App\\Entity/App\\Entity\\Person/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Person/g' *

echo 'copy Security from person'
cp -p ~/workspace/magenta/trivex/api/person/api/src/Security/* ~/workspace/magenta/trivex/api/admin/src/Security

echo 'copy Subscriber from person'
cp -p ~/workspace/magenta/trivex/api/person/api/src/Doctrine/Subscriber/* ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
cd ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
sed -i -- 's/App\\Util;/App\\Util\\Person;/g' *
sed -i -- 's/App\\Util\\/App\\XXXUtil\\Person\\/g' *
sed -i -- 's/App\\Entity/App\\XXXEntity\\Person/g' *

echo 'copy Message from person'
rm -R -f ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Person/*
cp -R -p ~/workspace/magenta/trivex/api/person/api/src/Message/Entity/* ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Person
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Person
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Person/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Person/g' *
cd ~/workspace/magenta/trivex/api/admin/src/Message/Entity/Person/V1
sed -i -- 's/App\\Message\\Entity/App\\Message\\Entity\\Person/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Person/g' *
sed -i -- 's/App\\Util/App\\Util\\Person/g' *

echo 'copy Util from person'
# libraries\component\utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/admin/libraries/component/utils
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src/Util/*  ~/workspace/magenta/trivex/api/admin/src/Util/Person
cp ~/workspace/magenta/trivex/api/person/api/src/Util/AppUtil.php ~/workspace/magenta/trivex/api/admin/src/Util/Person
cd ~/workspace/magenta/trivex/api/admin/src/Util/Person
sed -i -- 's/App\\Util;/App\\Util\\Person;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Person/g' *

echo 'copy Command from person'
cp -R -p ~/workspace/magenta/trivex/api/person/api/src/Command/* ~/workspace/magenta/trivex/api/admin/src/Command/Person;
rm -f ~/workspace/magenta/trivex/api/admin/src/Command/Person/AwsSqsWorkerCommand.php;
cd ~/workspace/magenta/trivex/api/admin/src/Command/Person;
sed -i -- 's/App\\Util/App\\Util\\Person/g' *
sed -i -- 's/App\\Command;/App\\Command\\Person;/g' *
sed -i -- 's/App\\Entity/App\\Entity\\Person/g' *
