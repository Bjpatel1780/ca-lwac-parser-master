#!/bin/bash
now=$(date +"%Y-%m-%d-%H-%M")
echo "Starting Time is $(date +"%Y-%m-%d-%H:%M")"
mv /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import_$now
mkdir -p /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import
rm -rf /var/www/lwacparser/outputs
docker run -v /var/www/lwacparser/:/var/www/lwacparser/:rw lwacparser:1.0
cp -rf /var/www/lwacparser/outputs/*.tsv  /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import
cp -rf /var/www/lwacparser/docs/*.pdf /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import
SAVEIFS=$IFS
IFS=$(echo -en "\n\b")
TSV_FILES=`cd /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import && ls *.tsv`
#echo "$TSV_FILES"
for i in $TSV_FILES
do
   echo $i
   IMPORT_RESULT=`cd /var/www/numiko/app/citizens-advice && app/console citizensadvice:trial:import $i --dry-run`
   echo $IMPORT_RESULT
    if  [[ "$IMPORT_RESULT" == *"Dry run complete. 1 trials validated successfully"* ]]; then
       SUCCESS_RECORDS+=("$i")
    elif [[ "$IMPORT_RESULT" == *"Missing"* ]]; then
       FAILURE_RECORDS+=("$i")
    else
       OTHER_RECORDS+=("$i")
    fi
done
printf "Number of Sucessful Records: %s\n" "${#SUCCESS_RECORDS[@]}"
echo "${SUCCESS_RECORDS[@]}"
printf "Number of Failure Records: %s\n" "${#FAILURE_RECORDS[@]}"
echo "${FAILURE_RECORDS[@]}"
printf "Number of Other Records: %s\n" "${#OTHER_RECORDS[@]}"
echo "${OTHER_RECORDS[@]}"

#echo "Starting time : $now

#Ending Time: $(date +"%Y-%m-%d-%H:%M") " | mail -s "LWAC IMPORT Log in Clone Environment on $now" darren.mccall@citizensadvice.org.uk devadath.tabeti@citizensadvice.org.uk ken.wan@citizensadvice.org.uk zhelyan.panchev@citizensadvice.org.uk
IFS=$SAVEIFS
