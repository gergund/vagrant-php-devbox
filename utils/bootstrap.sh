#!/usr/bin/env bash

if [ -f /tmp/.provisioned ]; then
	
	/bin/echo "Provisioned"
	exit 0		
else 
	/bin/echo  "Configure yum repos ..."
		/usr/bin/sudo yum install -y https://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm && /bin/echo 'EPEL - done'
		if [[ $? != 0 ]]; then
			/usr/bin/curl --retry 3 -o /tmp/epel-release-6-8.noarch.rpm https://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm 
			/usr/bin/sudo yum localinstall -y /tmp/epel-release-6-8.noarch.rpm
		fi
		/usr/bin/sudo yum clean all
	/bin/echo  "Done"

	/bin/echo "Install ansible ..."
		/usr/bin/sudo yum install -y ansible libselinux-python && /bin/echo 'Install of ansible - done'
	/bin/echo "Done"

	/bin/echo "Run ansible to provision configuration ..."
		/usr/bin/sudo /usr/bin/ansible-playbook /vagrant/ansible/site.yml --connection=local
	/bin/echo "Done"

	touch /tmp/.provisioned
	exit 0
fi
