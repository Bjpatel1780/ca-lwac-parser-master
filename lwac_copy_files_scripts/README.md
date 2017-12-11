###LWAC Scripts for Copy to and Download from S3 Bucket

###Setup

---- We created user id witnessbox-uploads in cita-dev account <br />
---- witnessbox-uploads accesskey and secret key are use to upload and download files from S3 Bucket <br />
 --- if witnessbox-uploads user id security credentials is changed we need to change it in script also <br />

###1. Lwacfiles_toS3.bat
---- Scripts is placed in dev-witnessbox-pop3 instance (cita-dev) - 172.24.21.128 <br /> 
---- Location: C:\Users\witness.service\Desktop <br />
---- Upload files (*.docs and *.PDF ) from "For RAVI/YYYY-MM-dd" folder to S3 Bucket  witnessbox-lwac-files-upload (cita-dev) <br />
---- Script will run on 1.00 PM EveryDay <br />


###2. LWACCopyfilesfromS3.sh
---- This script will be called by /var/www/lwacparser/lwac_import_CronJob.sh <br />
---- Scripts is placed in prod  instance ca-ap-witnessbox (cita-prod) <br />
---- Location: /home/ec2-user <br />
---- Download files (*.docs and *.PDF ) from S3 Bucket  witnessbox-lwac-files-upload to location /home/ec2-user/docs_source of ca-ap-witnessbox prod instance <br />
