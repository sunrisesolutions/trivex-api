echo 'copy to configuration'
mkdir -p ~/workspace/magenta/trivex/api/configuration/api/utils
rm -R -f ~/workspace/magenta/trivex/api/configuration/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/configuration/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/configuration/api/utils/

echo 'copy to user'
mkdir -p ~/workspace/magenta/trivex/api/user/api/utils
rm -R -f ~/workspace/magenta/trivex/api/user/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/user/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/user/api/utils/


echo 'copy to authorisation'
mkdir -p ~/workspace/magenta/trivex/api/authorisation/api/utils
rm -R -f ~/workspace/magenta/trivex/api/authorisation/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/authorisation/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/authorisation/api/utils/

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
mkdir -p ~/workspace/magenta/trivex/api/person/api/utils
rm -R -f ~/workspace/magenta/trivex/api/person/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/person/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/person/api/utils/

echo 'copy to organisation'
mkdir -p ~/workspace/magenta/trivex/api/organisation/api/utils
rm -R -f ~/workspace/magenta/trivex/api/organisation/api/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/organisation/api/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/organisation/api/utils/

echo 'done'


