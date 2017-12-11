#!/bin/bash
now=$(date +"%Y-%m-%d-%H-%M")
echo "Starting Time is $(date +"%Y-%m-%d-%H:%M")i"

Failure_log="/var/www/lwacparser/src/duplicatesFailures/FailureRecords_$now.txt"
Duplicate_log="/var/www/lwacparser/src/duplicatesFailures/DuplicateRecords_$now.txt"
Other_log="/var/www/lwacparser/src/duplicatesFailures/OtherRecords_$now.txt"
Success_log="/var/www/lwacparser/src/duplicatesFailures/SucessRecords_$now.txt"


mv /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import_$now
mkdir -p /var/www/numiko/app/citizens-advice/src/CitizensAdvice/Bundle/TrialBundle/Resources/import

rm -rf /var/www/lwacparser/outputs
rm -rf /var/www/lwacparser/src/txtdirhtml
rm -rf /var/www/lwacparser/src/duplicatesFailures
mkdir /var/www/lwacparser/src/duplicatesFailures

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
   IMPORT_RESULT=`cd /var/www/numiko/app/citizens-advice && app/console citizensadvice:trial:import $i`

   echo $IMPORT_RESULT
    if  [[ "$IMPORT_RESULT" == *"Import successful. 1 trials created"* ]]; then
       SUCCESS_RECORDS+=("$i")
    elif [[ "$IMPORT_RESULT" == *"Missing"* ]]; then
       FAILURE_RECORDS+=("$i")
    elif [[ "$IMPORT_RESULT" == *"Duplicate URNs ignored"* ]]; then
       DUPLICATE_RECORDS+=("$i")
    else
	
       OTHER_RECORDS+=("$i")
    fi
done
printf "Number of Sucessful Records: %s\n" "${#SUCCESS_RECORDS[@]}"
echo "${SUCCESS_RECORDS[@]}"
printf "%s\n" "${SUCCESS_RECORDS[@]}">${Success_log}
sed -i -e 's/.tsv/.txt/g' ${Success_log}

printf "Number of Failure Records: %s\n" "${#FAILURE_RECORDS[@]}"
echo "${FAILURE_RECORDS[@]}"
printf "%s\n" "${FAILURE_RECORDS[@]}">${Failure_log}
sed -i -e 's/.tsv/.txt/g' ${Failure_log}

printf "Number of Duplicate Records: %s\n" "${#DUPLICATE_RECORDS[@]}"
echo "${DUPLICATE_RECORDS[@]}"
printf "%s\n" "${DUPLICATE_RECORDS[@]}" >${Duplicate_log}
sed -i -e 's/.tsv/.txt/g' ${Duplicate_log}

printf "Number of Other Records: %s\n" "${#OTHER_RECORDS[@]}"
echo "${OTHER_RECORDS[@]}"
printf "%s\n" "${OTHER_RECORDS[@]}" >${Other_log}
sed -i -e 's/.tsv/.txt/g' ${Other_log}

IFS=$SAVEIFS
php /var/www/lwacparser/src/LwacDuplicatesEmailrun.php
mv /var/www/lwacparser/src/Duplicate.csv /var/www/lwacparser/src/Duplicatecsvs/Duplicates_$now.csv

echo "The Duplicate and Rejected LWAC's" | mailx -s "Duplicates and Rejected Lwacs email" -a /var/www/lwacparser/src/Duplicatecsvs/Duplicates_$now.csv devadath.tabeti@citizensadvice.org.uk georgina.craven@citizensadvice.org.uk darren.mccall@citizensadvice.org.uk lisa.mclain@wsncc.citizensadvice.org.uk emma.attwell@wsncc.citizensadvice.org.uk rhiannon.evans@citizensadvice.org.uk jonathan.jones@wsncc.citizensadvice.org.uk abbie.houghton@citizensadvice.org.uk


