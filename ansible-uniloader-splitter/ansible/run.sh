#! /bin/bash

case $1 in
	"debug" )
		STARTER=--start-at-task="startmeup"
esac

ansible-playbook -i ansible-hosts \
       --private-key ~/loway/settings_mac/keys/interventi_remoti_lenz -u lorenzo --become-user root spartan-asterisk.yml $STARTER


