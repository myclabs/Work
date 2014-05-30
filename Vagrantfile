Vagrant.configure("2") do |config|

    config.vm.box = "precise32"
    config.vm.box_url = "http://files.vagrantup.com/precise32.box"

    config.vm.provider "virtualbox" do |v|
        v.customize ["modifyvm", :id, "--memory", 1024]
    end

    config.vm.network "private_network", ip: "192.168.50.4"
    config.vm.synced_folder ".", "/vagrant", type: "nfs"

    config.vm.provision :shell, :path => "vagrant/bootstrap.sh"

end
