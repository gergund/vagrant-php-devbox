# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  config.vm.box = "centos/6"
  config.vm.box_check_update = true
  config.vm.synced_folder ".", "/vagrant"

  config.vm.define :app do |app|
	app.vm.provider "virtualbox" do |vb|
		vb.customize ['modifyvm', :id, '--memory', 1024]
	end
  	app.vm.network :forwarded_port, guest: 80, host: 80
   	app.vm.network :forwarded_port, guest: 443, host: 443

  	app.vm.network "private_network", ip: "192.168.33.10"
	#For Linux host system
	#app.vm.provision :shell, :inline => '/vagrant/utils/bootstrap.sh'
	#For Windows
	#app.vm.provision :shell, :inline => 'yum install -y dos2unix && dos2unix /vagrant/utils/bootstrap.sh && /bin/bash /vagrant/utils/bootstrap.sh'
	#For Windows alt solution
    app.vm.provision :shell, :inline => 'mkdir -p /vagrant-php-devbox && cd /vagrant-php-devbox && yum install -y git && git clone https://github.com/gergund/vagrant-php-devbox.git . && /bin/bash utils/bootstrap.sh'
  end

end
