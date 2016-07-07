#! /bin/bash


echo Sloggatron Is Running . . . 

## find all agents 
declare -a members=(`asterisk -rx "queue show" | grep "/" | cut -d"(" -f1`)

##find all queues
declare -a queues=(`asterisk -rx "queue show" | cut -d " " -f1`  )

## now loop through the above arrays
for i in "${members[@]}"
do
   for q in "${queues[@]}"
   do        
	##attempts to remove each agent from each queue
	cmd="queue remove member $i from $q"
        asterisk -rx "$cmd"
        echo Action Performed: $cmd
   done
done

echo . . . done
