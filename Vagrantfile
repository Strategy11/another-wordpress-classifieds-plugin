# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

VM_HOSTNAME = "awpcp.local"
VM_ADDRESS = "10.10.10.2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.synced_folder '.', '/vagrant', nfs:true

  config.vm.provision "shell", inline: "sed -i s/enabled=1/enabled=0/ /etc/yum.repos.d/fedora-updates-testing.repo"
  # config.vm.provision "shell", path: "scripts/fix-slow-dns.sh"
  config.vm.provision "shell", path: "scripts/vagrant-bootstrap.sh"

  config.vm.define :development do |local|
    local.vm.box = "chef/fedora-20"
    local.vm.hostname = VM_HOSTNAME
    local.vm.network :private_network, ip: VM_ADDRESS

    local.vm.provider :virtualbox do |vb|
      vb.customize ['modifyvm', :id, '--cpus', '4']
      vb.customize ['modifyvm', :id, '--ioapic', 'on']
      vb.customize ['modifyvm', :id, '--memory', '2048']

      vb.name = "awpcp-development"

      # https://github.com/mitchellh/vagrant/issues/2786
      vb.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
      # https://github.com/asm-helpful/helpful-web/commit/b0f624c54e9492f5d6eee9d4674e6865ac622cef
      vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
    end
  end

end
