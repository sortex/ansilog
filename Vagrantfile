# Ansilog's Vagrnatfile
#
# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

	# Archlinux:
	#config.vm.box = "arch"
	#config.vm.box_url = "https://www.dropbox.com/s/86vfxbito9avg3m/packer_arch_virtualbox.box"

	# Ubuntu or Debian:
	config.vm.box = "ubuntu/trusty64"
	config.vm.box_url = "https://vagrantcloud.com/ubuntu/boxes/trusty64/versions/14.04/providers/virtualbox.box"

	config.vm.network :forwarded_port, guest: 80, host: 8080
	config.vm.network :private_network, ip: "33.33.33.10"

	config.vm.synced_folder ".", "/vagrant", type: "nfs"

	config.vm.define :ansilog do |override|
		override.vm.hostname = "ansilog-vm"
	end

	config.vm.provision 'ansible' do |ansible|
		ansible.playbook = 'boot/playbooks/provision-debian.yml'
		ansible.raw_arguments = [ '--diff' ]
		ansible.inventory_path = 'boot/hosts'
		ansible.limit = 'ansilog-vm'
#		ansible.verbose = 'vvv'
	end

	# Special settings for VirtualBox machine
	# Play with whatever setting you want
	config.vm.provider :virtualbox do |vb|
		vb.customize ["modifyvm", :id, "--name", "ansilog"]
		vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
		vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
		vb.customize ["modifyvm", :id, "--memory", 2048]
		vb.customize ["modifyvm", :id, "--cpus", 3]
#		vb.customize ["modifyvm", :id, "--cpuexecutioncap", "90"]
#		vb.customize ["modifyvm", :id, "--ioapic", "on"]
	end

end
