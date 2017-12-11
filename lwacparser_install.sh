#!/bin/bash
INSTALL_LOCATION=/var/www/lwacparser
echo $INSTALL_LOCATION
mkdir -p $INSTALL_LOCATION $INSTALL_LOCATION/logs $INSTALL_LOCATION/docs_archive $INSTALL_LOCATION/outputs $INSTALL_LOCATION/docs $INSTALL_LOCATION/src/duplicatesFailures $INSTALL_LOCATION/src/txtdirhtml  $INSTALL_LOCATION/src/Duplicatecsvs
yum -y install docker mailx
service docker restart
cp -rf ./src ./docker ./scripts $INSTALL_LOCATION
cd $INSTALL_LOCATION/docker && docker build -t lwacparser:1.0 .
cp -rf $INSTALL_LOCATION/scripts/lwac_import_CronJob.sh  $INSTALL_LOCATION/
chmod -R 755 $INSTALL_LOCATION
mkdir -p /home/ec2-user/docs_source
chown -R ec2-user:ec2-user /home/ec2-user/docs_source
chmod -R 755 /home/ec2-user/docs_source
mkdir -p /var/log/lwacparser/
touch /var/log/lwacparser/lwacparser_cronjob.log
echo "00 14  * * * root /var/www/lwacparser/lwac_import_CronJob.sh >> /var/log/lwacparser/lwacparser_cronjob.log 2>&1" > /etc/cron.d/lwacparserjob
#