echo 'copy Util from user'
cp -p ~/workspace/magenta/trivex/api/utils/src/Security/DecisionMakingInterface.php ~/workspace/magenta/trivex/api/admin/src/Security

echo 'clear Admin Doctrine/Subscriber folder'
rm -R -f ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber/*

echo 'copy to configuration'
mkdir -p ~/workspace/magenta/trivex/api/configuration/api/utils
rm -R -f ~/workspace/magenta/trivex/api/configuration/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/configuration/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/configuration/api/utils/

echo 'copy to user'
sh ~/workspace/magenta/trivex/api/utils/shell/build-user.sh
cd ~/workspace/magenta/trivex/api

echo 'copy to authorisation'
sh ~/workspace/magenta/trivex/api/utils/shell/build-auth.sh
cd ~/workspace/magenta/trivex/api

echo 'copy to event'
sh ~/workspace/magenta/trivex/api/utils/shell/build-event.sh
cd ~/workspace/magenta/trivex/api


echo 'copy to messaging'
sh ~/workspace/magenta/trivex/api/utils/shell/build-messaging.sh
cd ~/workspace/magenta/trivex/api

echo 'copy to person'
sh ~/workspace/magenta/trivex/api/utils/shell/build-person.sh
cd ~/workspace/magenta/trivex/api

echo 'copy to organisation'
sh ~/workspace/magenta/trivex/api/utils/shell/build-organisation.sh
cd ~/workspace/magenta/trivex/api


echo 'fix Subscriber'
cd ~/workspace/magenta/trivex/api/admin/src/Doctrine/Subscriber
sed -i -- 's/App\\XXXUtil/App\\Util/g' *
sed -i -- 's/App\\XXXEntity/App\\Entity/g' *
cd ~/workspace/magenta/trivex/api

echo 'copy to admin'

echo 'done'