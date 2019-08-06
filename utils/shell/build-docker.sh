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
mkdir -p ~/workspace/magenta/trivex/api/event/api/utils
rm -R -f ~/workspace/magenta/trivex/api/event/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/event/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/event/api/utils/

echo 'copy to messaging'
mkdir -p ~/workspace/magenta/trivex/api/messaging/api/utils
rm -R -f ~/workspace/magenta/trivex/api/messaging/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/messaging/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/messaging/api/utils/

echo 'copy to person'
echo 'copy to user'
sh ~/workspace/magenta/trivex/api/utils/shell/build-person.sh
cd ~/workspace/magenta/trivex/api

echo 'copy to organisation'
sh ~/workspace/magenta/trivex/api/utils/shell/build-organisation.sh
cd ~/workspace/magenta/trivex/api


echo 'copy to admin'


echo 'done'


