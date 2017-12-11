#! /bin/bash
#
#chown -R ec2-user:ec2-user /home/ec2-user/docs_source
#chmod -R 755 /home/ec2-user/docs_source
#
now=$(date +"%Y-%m-%d-%H-%M")
echo "Starting Time is $(date +"%Y-%m-%d-%H:%M")"

declare -a dirs
i=1
cd /home/ec2-user/docs_source
for d in */
do
    dirs[i++]="${d%/}"
done
echo $i
if [ "$i" -gt 1 ];then
for((i=1;i<=${#dirs[@]};i++))
do
   rm -rf /var/www/lwacparser/docs
   mkdir -p /var/www/lwacparser/docs
   echo "Copying files in ${dirs[i]} to docs! "
   cp ${dirs[i]}/* /var/www/lwacparser/docs
   echo "Copy complete!"
   sh /var/www/lwacparser/scripts/lwac_import_run.sh >> /var/www/lwacparser/logs/lwac_import_full_run_folder_${dirs[i]}_"${now}".log
   echo "lwac_import_full_run_folder_${dirs[i]}_"${now}".log"
   echo "Moving the ${dirs[i]} to docs_archive"
   mv ${dirs[i]} /var/www/lwacparser/docs_archive/${dirs[i]}_"${now}"
   echo "Move complete!"
   echo "Removing old Archival Files"
   find /var/www/lwacparser/docs_archive  -mindepth 1 -maxdepth 1 -type d -ctime +7 | xargs rm -rf
   find /var/www/lwacparser/logs -mindepth 1 -maxdepth 1 -ctime +10 | xargs rm -rf
   find /var/www/lwacparser/src/Duplicatecsvs -mindepth 1 -maxdepth 1 -ctime +7 | xargs rm -rf
   echo "Mailing the Log file"
   echo "Starting time : $now
   Ending Time: $(date +"%Y-%m-%d-%H:%M") " | mailx -s "LWAC Import Result in Prod Environment $now" \
     -a /var/www/lwacparser/logs/lwac_import_full_run_folder_${dirs[i]}_"${now}".log \
     bhavesh.patel@citizensadvice.org.uk abbie.houghton@citizensadvice.org.uk ca-devops@citizensadvice.org.uk
done

else

echo "No new files!"

fi

now=$(date +"%Y-%m-%d-%H-%M")
echo "Ending Time is $(date +"%Y-%m-%d-%H:%M")"
#