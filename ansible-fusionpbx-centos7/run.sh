#! /bin/bash

#ansible-playbook -i ansible-hosts \
#      --private-key some.key  \
#      -u loway --become-user root \
#      fsw.yml


ansible-playbook -i ansible-hosts \
      fsw.yml


# --start-at-task RST \
      