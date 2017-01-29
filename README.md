[Centos 6/Nginx/PHP 5.6/MySQL 5.6]

Developer Vagrant instance all-in-one

[Structure]
.
├── ansible
│   ├── group_vars
│   │   └── all      - Variables
│   ├── roles        - Ansible roles
│   │   ├── common 
│   │   ├── mysql
│   │   ├── nginx
│   │   └── php-fpm
│   └── site.yml     - Main playbook
├── utils
│   └── bootstrap.sh - Shell script to bootstrap instance with ansible
├── Vagrantfile      - Vagrant file
└── www              - Project code 
    └── index.php

[Init]

 - Install VirtualBox
 - Install Vagrant


 - To init instance:
	$vagrant up
