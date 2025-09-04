# -*- mode: ruby -*-
# vi: set ft=ruby :

SNIPEIT_SH_URL= "https://raw.githubusercontent.com/grokability/snipe-it/master/snipeit.sh"
NETWORK_BRIDGE= "en0: Wi-Fi (AirPort)"

Vagrant.configure("2") do |config|
  config.vm.define "bionic" do |bionic|
    bionic.vm.box = "ubuntu/bionic64"
    bionic.vm.hostname = 'bionic'
    bionic.vm.network "forwarded_port", guest: 80, host: 8080
    bionic.vm.synced_folder ".", "/vagrant", :owner => 'www-data',
      :group => 'vagrant', :mount_options => ['dmode=775', 'fmode=775']
    bionic.vm.provision "ansible_local" do |ansible|
      ansible.playbook = "ansible/ubuntu/vagrant_playbook.yml"
    end
  end

  config.vm.define "xenial" do |xenial|
    xenial.vm.box = "ubuntu/xenial64"
    xenial.vm.hostname = 'xenial'
    xenial.vm.network "forwarded_port", guest: 80, host: 8080
    xenial.vm.synced_folder ".", "/vagrant", :owner => 'www-data',
      :group => 'vagrant', :mount_options => ['dmode=775', 'fmode=775']
    xenial.vm.provision "ansible_local" do |ansible|
      ansible.playbook = "ansible/ubuntu/vagrant_playbook.yml"
    end
  end

  config.vm.define "trusty" do |trusty|
    trusty.vm.box = "ubuntu/trusty32"
    trusty.vm.hostname = 'trusty'
    trusty.vm.network "forwarded_port", guest: 80, host: 8080
    trusty.vm.synced_folder ".", "/vagrant", :owner => 'www-data',
      :group => 'vagrant', :mount_options => ['dmode=775', 'fmode=775']
    trusty.vm.provision "ansible_local" do |ansible|
      ansible.playbook = "ansible/ubuntu/vagrant_playbook.yml"
    end
  end

  config.vm.define "centos7" do |centos7|
    centos7.vm.box = "centos/7"
    centos7.vm.hostname = 'centos7'
    centos7.vm.network "public_network", bridge: NETWORK_BRIDGE
    centos7.vm.provision :shell, :inline => "sudo yum -y update"
    centos7.vm.provision :shell, :inline => "yum install -y wget"
    centos7.vm.provision :shell, :inline => "wget #{SNIPEIT_SH_URL}"
    centos7.vm.provision :shell, :inline => "chmod 755 snipeit.sh"
  end

  config.vm.define "centos6" do |centos6|
    centos6.vm.box = "centos/6"
    centos6.vm.hostname = 'centos6'
    centos6.vm.network "public_network", bridge: NETWORK_BRIDGE
    centos6.vm.provision :shell, :inline => "sudo yum -y update"
    centos6.vm.provision :shell, :inline => "wget #{SNIPEIT_SH_URL}"
    centos6.vm.provision :shell, :inline => "chmod 755 snipeit.sh"
  end

  config.vm.define "jessie" do |jessie|
    jessie.vm.box = "debian/jessie64"
    jessie.vm.hostname = 'debian8'
    jessie.vm.network "public_network", bridge: NETWORK_BRIDGE
    jessie.vm.provision :shell, :inline => "wget #{SNIPEIT_SH_URL}"
    jessie.vm.provision :shell, :inline => "chmod 755 snipeit.sh"
  end

  config.vm.define "stretch" do |stretch|
    stretch.vm.box = "debian/stretch64"
    stretch.vm.hostname = 'debian9'
    stretch.vm.network "public_network", bridge: NETWORK_BRIDGE
    stretch.vm.provision :shell, :inline => "wget #{SNIPEIT_SH_URL}"
    stretch.vm.provision :shell, :inline => "chmod 755 snipeit.sh"
  end

  config.vm.define "fedora27" do |fedora27|
    fedora27.vm.box = "fedora/27-cloud-base"
    fedora27.vm.hostname = 'fedora27'
    fedora27.vm.network "public_network", bridge: NETWORK_BRIDGE
    fedora27.vm.provision :shell, :inline => "dnf -y install wget"
    fedora27.vm.provision :shell, :inline => "wget #{SNIPEIT_SH_URL}"
    fedora27.vm.provision :shell, :inline => "chmod 755 snipeit.sh"
  end

    config.vm.define "fedora26" do |fedora26|
    fedora26.vm.box = "fedora/26-cloud-base"
    fedora26.vm.hostname = 'fedora26'
    fedora26.vm.network "public_network", bridge: NETWORK_BRIDGE
    fedora26.vm.provision :shell, :inline => "dnf -y install wget"
    fedora26.vm.provision :shell, :inline => "wget #{SNIPEIT_SH_URL}"
    fedora26.vm.provision :shell, :inline => "chmod 755 snipeit.sh"
  end

  config.vm.define "freebsd" do |freebsd|
    freebsd.vm.box = "freebsd/FreeBSD-11.2-RELEASE"
    freebsd.vm.hostname = 'freebsd12'
    freebsd.vm.network "forwarded_port", guest: 80, host: 8080
    freebsd.vm.network "forwarded_port", guest:3306, host:3306 # mysql    
    freebsd.vm.network "private_network", type: "dhcp"
    freebsd.ssh.shell = "sh"
    freebsd.vm.base_mac = "080027D14C66"
    freebsd.vm.synced_folder ".", "/vagrant", :nfs => true, id: "vagrant-root",
    :mount_options => ['rw', 'vers=3', 'tcp', 'actimeo=2']    
    freebsd.vm.provision "shell", inline: <<-SHELL
        pkg install -y python27;
    SHELL
    freebsd.vm.provision "ansible" do |ansible|
        ansible.playbook = "ansible/freebsd/vagrant_playbook.yml"
    end    
  end  
end
