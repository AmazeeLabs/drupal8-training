# -*- mode: ruby -*-
# vi: set ft=ruby :

HOSTNAME = "d8training.lo"
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "precise32"
  config.vm.box_url = "http://transfer.amazeelabs.com/vagrant/precise32.box"
  config.vm.hostname = HOSTNAME
  config.vm.network :private_network, :ip => '192.168.111.42'
  # config.ssh.forward_agent = true
  config.vm.synced_folder ".", "/home/vagrant/public_html" , :nfs => true

  config.vm.provider :virtualbox do |vm|
    vm.customize ["modifyvm", :id, "--memory", "2048"]
    vm.customize ["modifyvm", :id, "--ioapic", "on"]
  end

  if not RUBY_PLATFORM.downcase.include?("mswin")
    config.vm.provider :virtualbox do |vm|
      vm.customize ["modifyvm", :id, "--cpus", `awk "/^processor/ {++n} END {print n}" /proc/cpuinfo 2> /dev/null || sh -c 'sysctl hw.logicalcpu 2> /dev/null || echo ": 2"' | awk \'{print \$2}\' `.chomp ]
    end
  else
    config.vm.provider :virtualbox do |vm|
      vm.customize ["modifyvm", :id, "--cpus", "2"]
    end
  end

  config.vm.provision :chef_solo do |chef|
    chef.cookbooks_path = "vagrant"
    chef.log_level = ENV['CHEF_LOG'] || "info"

    chef.json = {
        ":hostname" => HOSTNAME
    }

    chef.add_recipe("common")
    chef.add_recipe("common::motd")
    chef.add_recipe("php::php54")
    chef.add_recipe("php::php-additions")
    chef.add_recipe("composer")
    chef.add_recipe("drush")
    chef.add_recipe("drupal::drupal-settings")
    chef.add_recipe("drupal::mysql-database")
  end
end
