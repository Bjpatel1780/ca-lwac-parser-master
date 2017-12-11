@ECHO OFF
SET log_location=C:\Users\witness.service\Desktop\logs
set today_date=%DATE:~10,4%-%DATE:~4,2%-%DATE:~7,2%

echo "##########Program started %date%##########" >> %log_location%/%today_date%.txt
SET title="LWAC Folder copy to S3"
TITLE %title%


SET input_directory=C:\Users\witness.service\Desktop\For RAVI\%today_date%
SET output_directory=C:\Users\witness.service\Desktop\docs_archives
SET s3_bucket=witnessbox-lwac-files-upload
SET AWS_ACCESS_KEY_ID=XXYYZZ
SET AWS_SECRET_ACCESS_KEY=XXYYZZ

if not exist "%input_directory%"  goto :exit

echo "Going to Upload files to s3" >> %log_location%/%today_date%.txt
echo "aws s3 cp %input_directory% s3://%s3_bucket%/%today_date% --recursive" >> %log_location%/%today_date%.txt
aws s3 cp "%input_directory%" s3://%s3_bucket%/%today_date%  --recursive

move /Y "%input_directory%" %output_directory%/%today_date%
echo "Moved the folder to archive location" >> %log_location%/%today_date%.txt
echo "##########Program end %date%##########" >> %log_location%/%today_date%.txt

:exit
