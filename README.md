# ca-lwac-parser
Parse lwac files for input into a database

Report-Duplicates-Rejected :

#Folders required
/var/www/lwacparser/docs
/var/www/lwacparser/docs_archive
/var/www/lwacparser/src/duplicatesFailures
/var/www/lwacparser/outputs
/var/www/lwacparser/src/txtdirhtml
/var/www/lwacparser/src/Duplicatecsvs
/var/www/lwacparser/logs

#lwacparser_install.sh

---- we can configure lwacparser setup using lwacparser_install.sh <br/>
---- This script will configure lwacparser setup in /var/www/lwacparser location <br/>
---- It will create required folders and installs packages <br/>
---- It will create a docker image lwacparser:1.0 <br/>
---- lwac_import_CronJob.sh will launch the docker container with image lwacparser:1.0 to process lwacs <br/>

#lwac_import_CronJob.sh

---- the doc files are pused to /home/ec2-user/docs_source from the POP3 scheduled daily job (at 10am)
     via SCP (s3 NOT used anymore)
     
---- this script will copy the folders from /home/ec2-user/docs_source to /var/www/lwacparser/docs<br />
---- It will create docker container with image lwacparser:1.0 and will start to process lwacs<br />
---- After Lwac Process documents  are moved to /var/www/lwacparser/docs_archive<br />
---- Logs files will be created in /var/www/lwacparser/logs<br />
