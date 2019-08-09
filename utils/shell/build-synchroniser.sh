#Util
mkdir -p ~/workspace/magenta/trivex/api/synchroniser/utils
rm -R -f ~/workspace/magenta/trivex/api/synchroniser/utils/*
cp -R ~/workspace/magenta/trivex/api/utils/libraries/component/utils/src ~/workspace/magenta/trivex/api/synchroniser/utils/
cp -R ~/workspace/magenta/trivex/api/utils/config ~/workspace/magenta/trivex/api/synchroniser/utils/

#User
echo 'copy Entity from user'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Entity/User
cp -R -p ~/workspace/magenta/trivex/api/user/api/src/Entity/* ~/workspace/magenta/trivex/api/synchroniser/src/Entity/User
cd ~/workspace/magenta/trivex/api/synchroniser/src/Entity/User
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *
sed -i -- 's/App\\Util/App\\Util\\User/g' *
sed -i -- 's/App\\Repository/App\\Repository\\User/g' *

echo 'copy Repository from user'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Repository/User
cp -R -p ~/workspace/magenta/trivex/api/user/api/src/Repository/* ~/workspace/magenta/trivex/api/synchroniser/src/Repository/User
cd ~/workspace/magenta/trivex/api/synchroniser/src/Repository/User
sed -i -- 's/App\\Entity/App\\Entity\\User/g' *
sed -i -- 's/App\\Repository/App\\Repository\\User/g' *

#Organisation
echo 'copy Entity from organisation'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Organisation
cp -R -p ~/workspace/magenta/trivex/api/organisation/api/src/Entity/* ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Organisation
cd ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Organisation
sed -i -- 's/App\\Entity/App\\Entity\\Organisation/g' *
sed -i -- 's/App\\Util/App\\Util\\Organisation/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Organisation/g' *

echo 'copy Repository from organisation'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Organisation
cp -R -p ~/workspace/magenta/trivex/api/organisation/api/src/Repository/* ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Organisation
cd ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Organisation
sed -i -- 's/App\\Entity/App\\Entity\\Organisation/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Organisation/g' *


#Person
echo 'copy Entity from person'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Person
cp -R -p ~/workspace/magenta/trivex/api/person/api/src/Entity/* ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Person
cd ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Person
sed -i -- 's/App\\Entity/App\\Entity\\Person/g' *
sed -i -- 's/App\\Util/App\\Util\\Person/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Person/g' *

echo 'copy Repository from person'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Person
cp -R -p ~/workspace/magenta/trivex/api/person/api/src/Repository/* ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Person
cd ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Person
sed -i -- 's/App\\Entity/App\\Entity\\Person/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Person/g' *


#Authorisation
echo 'copy Entity from authorisation'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Authorisation
cp -R -p ~/workspace/magenta/trivex/api/authorisation/api/src/Entity/* ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Authorisation
cd ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Authorisation
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *
sed -i -- 's/App\\Util/App\\Util\\Authorisation/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Authorisation/g' *

echo 'copy Repository from authorisation'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Authorisation
cp -R -p ~/workspace/magenta/trivex/api/authorisation/api/src/Repository/* ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Authorisation
cd ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Authorisation
sed -i -- 's/App\\Entity/App\\Entity\\Authorisation/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Authorisation/g' *

#Messaging
echo 'copy Entity from messaging'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Messaging
cp -R -p ~/workspace/magenta/trivex/api/messaging/api/src/Entity/* ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Messaging
cd ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Messaging
sed -i -- 's/App\\Entity/App\\Entity\\Messaging/g' *
sed -i -- 's/App\\Util/App\\Util\\Messaging/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Messaging/g' *

echo 'copy Repository from messaging'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Messaging
cp -R -p ~/workspace/magenta/trivex/api/messaging/api/src/Repository/* ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Messaging
cd ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Messaging
sed -i -- 's/App\\Entity/App\\Entity\\Messaging/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Messaging/g' *

#Event
echo 'copy Entity from event'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Event
cp -R -p ~/workspace/magenta/trivex/api/event/api/src/Entity/* ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Event
cd ~/workspace/magenta/trivex/api/synchroniser/src/Entity/Event
sed -i -- 's/App\\Entity/App\\Entity\\Event/g' *
sed -i -- 's/App\\Util/App\\Util\\Event/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Event/g' *

echo 'copy Repository from event'
mkdir ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Event
cp -R -p ~/workspace/magenta/trivex/api/event/api/src/Repository/* ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Event
cd ~/workspace/magenta/trivex/api/synchroniser/src/Repository/Event
sed -i -- 's/App\\Entity/App\\Entity\\Event/g' *
sed -i -- 's/App\\Repository/App\\Repository\\Event/g' *