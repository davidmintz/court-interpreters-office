#!/usr/bin/env bash

# needs work!

FILES=($(s3cmd -c $HOME/.dh-sdny.s3cfg  ls s3://sdny/office|grep sql.gz|awk '{ print $4}'))
count=$((1 + ${#FILES[@]}))

echo "we have ${count} files."
if [ ${count} > 4 ];
then
    num_to_remove=$((count - 4))
    echo "need to remove: $num_to_remove files"
    for ((i=0; i<$num_to_remove; i++));
        do s3cmd  -c $HOME/.dh-sdny.s3cfg del ${FILES[i]}
    done
fi
echo OK
exit 0
