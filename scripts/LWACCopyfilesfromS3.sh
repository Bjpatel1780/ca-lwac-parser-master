#!/bin/sh
now=$(date +"%Y-%m-%d")
echo "##############Program Started on $now############"
s3_bucket=witnessbox-lwac-files-upload
output_location=/home/ec2-user/docs_source

export AWS_ACCESS_KEY_ID=XXYYZZ
export AWS_SECRET_ACCESS_KEY=XXYYZZ

echo "Going to download files from S3"
aws s3 cp s3://$s3_bucket/$now $output_location/$now --recursive
echo "Downloaded the files and folders"
echo "##############Program Ended on $now############"
